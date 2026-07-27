<?php

namespace App\Services\V1\CartService;

use App\Models\CartItem;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{

    public function getCart(User $user): Collection
    {
        return $user->cartItems()->with('meal.primaryImage')->get();
    }

    public function cartSummary(User $user)
    {
        $items = $this->getCart($user);
        $subtotal = $items->sum(function ($item) {
            $unitPrice = $item->meal->discount_price ?? $item->meal->price;
            return $unitPrice * $item->quantity;
        });
        return [
            'items' => $items,
            'summary' => [
                'items_count' => $items->sum('quantity'),
                'subtotal' => $subtotal,
            ],
        ];
    }

    public function addItem(User $user, int $mealId, int $quantity)
    {
        $meal = Meal::find($mealId);
        if (!$meal) throw new \Exception('Meal not found.', 404);

        if (!$meal->is_available) throw new \Exception('Meal is not available.', 422);

        $existingItem = $user->cartItems()->where('meal_id', $mealId)->first();
        $existingQuantity = $existingItem ? $existingItem->quantity : 0;
        $totalQuantity = $existingQuantity + $quantity;
        if ($totalQuantity > $meal->stock_quantity) throw new \Exception('Insufficient stock.', 422);
        if ($existingItem) {
            $existingItem->update(['quantity' => $totalQuantity]);
            return $existingItem->fresh('meal.primaryImage');
        }
        return $user->cartItems()->create([
            'meal_id' => $mealId,
            'quantity' => $quantity,
        ])->load('meal.primaryImage');
    }
    public function updateQuantity(User $user, int $mealId, int $quantity): CartItem
    {
        $cartItem = $user->cartItems()->where('meal_id', $mealId)->first();
        if (!$cartItem) {
            throw new \Exception('Item not found in cart.', 404);
        }
        $meal = $cartItem->meal;
        if (!$meal->is_available) {
            throw new \Exception('Meal is not available.', 422);
        }
        if ($quantity > $meal->stock_quantity) {
            throw new \Exception('Insufficient stock.', 422);
        }
        $cartItem->update(['quantity' => $quantity]);
        return $cartItem->fresh('meal.primaryImage');
    }
    public function removeItem(User $user, int $mealId)
    {
        $cartItem = $user->cartItems()->where('meal_id', $mealId)->first();
        if (!$cartItem) {
            throw new \Exception('Item not found in cart.', 404);
        }
        $cartItem->delete();
    }
    public function clearCart(User $user): void
    {
        $user->cartItems()->delete();
    }
}
