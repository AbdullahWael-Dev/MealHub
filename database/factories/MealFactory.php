<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Meal;
use App\Models\MealImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    protected $model = Meal::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         $price = fake()->randomFloat(2, 30, 300);
        $hasDiscount = fake()->boolean(40);
        return [
            'category_id' => Category::query()->inRandomOrder()->value('id')
                ?? Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => $price,
            'discount_price' => $hasDiscount
                ? round($price * fake()->randomFloat(2, 0.5, 0.9), 2)
                : null,
            'stock_quantity' => fake()->numberBetween(0, 100),
            'is_available' => fake()->boolean(85),
            'is_featured' => fake()->boolean(20),
            'preparation_time' => fake()->numberBetween(5, 60),
            'avg_rating' => fake()->randomFloat(2, 1, 5),
            'review_count' => fake()->numberBetween(0, 500),
        ];
    }
    public function configure(): static
    {
        return $this->afterCreating(function (Meal $meal) {
            $imagesCount = fake()->numberBetween(1, 4);

            for ($i = 0; $i < $imagesCount; $i++) {
                MealImage::factory()
                    ->for($meal)
                    ->state([
                        'sort_order' => $i,
                        'is_primary' => $i === 0, 
                    ])
                    ->create();
            }
        });
    }
}
