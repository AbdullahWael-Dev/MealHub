<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
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
            $favoriteMeals = $meals->shuffle()->take(fake()->numberBetween(2, 7));

            foreach ($favoriteMeals as $meal) {
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'meal_id' => $meal->id,
                ]);
            }
        }
    }
}
