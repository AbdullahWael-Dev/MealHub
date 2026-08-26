<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::query()->get();
        $meals = Meal::query()->get();

        if ($orders->isEmpty() || $meals->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            if ($order->items()->count() > 0) {
                continue;
            }

            $selectedMeals = $meals->shuffle()->take(fake()->numberBetween(1, 4));
            $itemsTotal = 0;

            foreach ($selectedMeals as $meal) {
                $quantity = fake()->numberBetween(1, 3);
                $unitPrice = $meal->discount_price ?? $meal->price;
                $subtotal = $unitPrice * $quantity;
                $itemsTotal += $subtotal;

                $order->items()->create([
                    'meal_id' => $meal->id,
                    'meal_name' => $meal->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update([
                'subtotal' => $itemsTotal,
                'total' => $itemsTotal + $order->delivery_fee - $order->discount,
            ]);
        }
    }
}
