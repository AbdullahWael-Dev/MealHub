<?php

namespace App\Services\V1\OrderServices;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use App\Services\V1\CouponServices\CouponService;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{

    private const TRANSITIONS = [
        'pending' => OrderStatus::Confirmed,
        'confirmed' => OrderStatus::Preparing,
        'preparing' => OrderStatus::OutForDelivery,
        'out_for_delivery' => OrderStatus::Delivered,
    ];
    public function  __construct(private readonly CouponService $couponService) {}

    public function createOrder(User $user, array $data)
    {
        $cartItems = $user->cartItems()->get();
        if ($cartItems->isEmpty()) {
            abort(422, 'Your cart is empty. Please add items to your cart before placing an order.');
        }
        $address = $user->addresses()->findOrFail($data['address_id']);
        return DB::transaction(function () use ($user, $data, $cartItems, $address) {
            $subtotal = 0;
            $orderItemsData = [];
            foreach ($cartItems as $cartItem) {
                $meal = Meal::whereKey($cartItem->meal_id)
                    ->lockForUpdate()
                    ->first();
                if (!$meal) {
                    abort(422, 'One of the meals in your cart is no longer available.');
                }
                if (!$meal->is_available) {
                    abort(422, "\"{$meal->name}\" is currently unavailable.");
                }
                if ($meal->stock_quantity < $cartItem->quantity) {
                    abort(422, "Not enough stock for \"{$meal->name}\". Only {$meal->stock_quantity} left.");
                }
                $unitPrice = $meal->discount_price ?? $meal->price;
                $itemSubtotal = $unitPrice * $cartItem->quantity;
                $subtotal += $itemSubtotal;
                $orderItemsData[] = [
                    'meal' => $meal,
                    'meal_name' => $meal->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $itemSubtotal,
                ];
            }
            $coupon = null;
            $discount = 0;
            if (!empty($data['coupon_code'])) {
                try {
                    $coupon = $this->couponService->validateCoupon($data['coupon_code'], (float) $subtotal);
                } catch (Exception $e) {
                    abort(422, $e->getMessage());
                }
                $discount = $this->couponService->calculateDiscount($coupon, (float) $subtotal);
            }
            $deliveryFee = 50;
            $total = round($subtotal - $discount + $deliveryFee, 2);
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,

                'status' => OrderStatus::Pending,
                'payment_method' => PaymentMethod::CashOnDelivery,
                'payment_status' => PaymentStatus::Pending,

                'shipping_name' => $address->recipient_name,
                'shipping_phone' => $address->phone,
                'shipping_city' => $address->city,
                'shipping_area' => $address->area,
                'shipping_street' => $address->street,
                'shipping_building' => $address->building,
                'shipping_floor' => $address->floor,
                'shipping_apartment' => $address->apartment,
                'shipping_landmark' => $address->landmark,

                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,

                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($orderItemsData as $item) {
                $order->items()->create([
                    'meal_id' => $item['meal']->id,
                    'meal_name' => $item['meal_name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
                $meal = $item['meal'];
                $meal->stock_quantity -= $item['quantity'];
                $meal->is_available = $meal->stock_quantity > 0;
                $meal->save();
            }
            if ($coupon) {
                $coupon->increment('used_count');
            }
            $user->cartItems()->delete();

            return $order->load(['items', 'address', 'coupon']);
        });
    }

    public function cancelOrder(User $user, int $orderId): Order
    {
        return DB::transaction(function () use ($user, $orderId) {
            $order = $user->orders()->with('items')->lockForUpdate()->findOrFail($orderId);
            if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Confirmed])) {
                abort(422, 'You can only cancel orders that are pending or Confirmed.');
            }
            foreach ($order->items as $item) {
                $meal = Meal::whereKey($item->meal_id)
                    ->lockForUpdate()
                    ->first();
                if ($meal) {
                    $meal->stock_quantity += $item->quantity;
                    $meal->is_available =  $meal->stock_quantity > 0;
                    $meal->save();
                }
                $order->status = OrderStatus::Cancelled;
                $order->cancelled_at = now();
                $order->save();

                return $order->fresh(['items', 'address', 'coupon']);
            }
        });
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $allowedNext = self::TRANSITIONS[$order->status->value] ?? null;
            if ($allowedNext === null || $allowedNext !== $newStatus) {
                abort(422, "Cannot move order from \"{$order->status->value}\" to \"{$newStatus->value}\".");
            }
            $timestampField = match ($newStatus) {
                OrderStatus::Confirmed => 'confirmed_at',
                OrderStatus::Preparing => 'preparing_at',
                OrderStatus::OutForDelivery => 'out_for_delivery_at',
                OrderStatus::Delivered => 'delivered_at',
            };
            $order->status = $newStatus;
            $order->{$timestampField} = now();
            if ($newStatus === OrderStatus::Delivered) {
                $order->payment_status = PaymentStatus::Paid;
            }
            $order->save();
            return $order->fresh('items');
        });
    }
}
