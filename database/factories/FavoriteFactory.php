<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'meal_id' => Meal::factory(),
        ];
    }
}
