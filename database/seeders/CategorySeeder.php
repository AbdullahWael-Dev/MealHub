<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Burgers & Sandwiches',
            'Pizza & Pasta',
            'Grilled Chicken',
            'Healthy Bowls',
            'Seafood',
            'Wraps & Rolls',
            'Breakfast',
            'Desserts',
            'Drinks',
            'Vegan & Vegetarian',
            'Family Meals',
            'Appetizers',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['name' => $name],
                [
                    'slug' => str($name)->slug()->toString(),
                    'image_path' => null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
