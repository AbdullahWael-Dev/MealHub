<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $meals = Meal::query()->get();
        $orders = Order::query()->get();

        if ($users->isEmpty() || $meals->isEmpty() || $orders->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            if ($order->items()->count() === 0) {
                continue;
            }

            $meal = $order->items()->inRandomOrder()->first()->meal;

            if (! $meal) {
                continue;
            }

            Review::firstOrCreate(
                [
                    'user_id' => $order->user_id,
                    'meal_id' => $meal->id,
                    'order_id' => $order->id,
                ],
                [
                    'rating' => fake()->numberBetween(3, 5),
                    'comment' => fake()->optional()->paragraph(),
                ]
            );
        }

        foreach ($meals as $meal) {
            if ($meal->reviews()->count() > 0) {
                continue;
            }

            $reviewUser = $users->random();
            $reviewOrder = $orders->where('user_id', $reviewUser->id)->first();

            if (! $reviewOrder) {
                continue;
            }

            Review::create([
                'user_id' => $reviewUser->id,
                'meal_id' => $meal->id,
                'order_id' => $reviewOrder->id,
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->sentence(),
            ]);
        }
    }
}
