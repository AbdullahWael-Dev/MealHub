<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\MealImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MealImageFactory extends Factory
{
    protected $model = MealImage::class;

    public function definition(): array
    {
        return [
            'meal_id' => Meal::factory(),
            'image_path' => $this->downloadRandomImage(),
            'alt_text' => fake()->words(3, true),
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }

    protected function downloadRandomImage(): string
    {
        $filename = 'meal-images/' . Str::uuid() . '.jpg';
        $url = 'https://picsum.photos/seed/' . Str::random(8) . '/800/600';

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Throwable $e) {
          
        }

        return $filename;
    }
}