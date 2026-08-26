<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $addresses = Address::query()->get();
        $coupons = Coupon::query()->get();
        $meals = Meal::query()->get();

        if ($users->isEmpty() || $addresses->isEmpty() || $meals->isEmpty()) {
            return;
        }

        $statusFlow = [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Preparing->value,
            OrderStatus::OutForDelivery->value,
            OrderStatus::Delivered->value,
        ];

        foreach ($users as $user) {
            $ordersCount = fake()->numberBetween(1, 4);

            for ($i = 0; $i < $ordersCount; $i++) {
                $address = $addresses->where('user_id', $user->id)->first() ?? $addresses->random();
                $coupon = $coupons->random();
                $status = $statusFlow[array_rand($statusFlow)];
                $subtotal = fake()->randomFloat(2, 80, 800);
                $discount = $coupon ? fake()->randomFloat(2, 0, 50) : 0;
                $deliveryFee = fake()->randomFloat(2, 10, 35);
                $total = max(0, $subtotal - $discount + $deliveryFee);

                $order = Order::create([
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'coupon_id' => $coupon?->id,
                    'status' => $status,
                    'payment_method' => PaymentMethod::CashOnDelivery->value,
                    'payment_status' => fake()->randomElement([
                        PaymentStatus::Pending->value,
                        PaymentStatus::Paid->value,
                        PaymentStatus::Failed->value,
                    ]),
                    'shipping_name' => $user->name,
                    'shipping_phone' => $address->phone,
                    'shipping_city' => $address->city,
                    'shipping_area' => $address->area,
                    'shipping_street' => $address->street,
                    'shipping_building' => $address->building ?? '12',
                    'shipping_floor' => $address->floor,
                    'shipping_apartment' => $address->apartment,
                    'shipping_landmark' => $address->landmark,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'delivery_fee' => $deliveryFee,
                    'total' => $total,
                    'notes' => fake()->optional()->sentence(),
                    'confirmed_at' => in_array($status, ['confirmed', 'preparing', 'out_for_delivery', 'delivered']) ? now()->subHours(rand(1, 72)) : null,
                    'preparing_at' => in_array($status, ['preparing', 'out_for_delivery', 'delivered']) ? now()->subHours(rand(1, 48)) : null,
                    'out_for_delivery_at' => in_array($status, ['out_for_delivery', 'delivered']) ? now()->subHours(rand(1, 24)) : null,
                    'delivered_at' => $status === 'delivered' ? now()->subHours(rand(1, 12)) : null,
                    'cancelled_at' => $status === 'cancelled' ? now()->subHours(rand(1, 10)) : null,
                ]);

                $selectedMeals = $meals->shuffle()->take(rand(1, 4));
                $orderTotal = 0;

                foreach ($selectedMeals as $meal) {
                    $quantity = fake()->numberBetween(1, 3);
                    $unitPrice = $meal->discount_price ?? $meal->price;
                    $subtotalItem = $unitPrice * $quantity;
                    $orderTotal += $subtotalItem;

                    $order->items()->create([
                        'meal_id' => $meal->id,
                        'meal_name' => $meal->name,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'subtotal' => $subtotalItem,
                    ]);
                }

                $order->update([
                    'subtotal' => $orderTotal,
                    'total' => $orderTotal - $discount + $deliveryFee,
                ]);
            }
        }
    }
}
