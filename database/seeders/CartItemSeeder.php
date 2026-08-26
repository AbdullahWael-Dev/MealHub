<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $meals = Meal::query()->get();

        if ($users->isEmpty() || $meals->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $itemsCount = fake()->numberBetween(0, 3);

            if ($itemsCount === 0) {
                continue;
            }

            $selectedMeals = $meals->shuffle()->take($itemsCount);

            foreach ($selectedMeals as $meal) {
                CartItem::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'meal_id' => $meal->id,
                    ],
                    [
                        'quantity' => fake()->numberBetween(1, 4),
                    ]
                );
            }
        }
    }
}
