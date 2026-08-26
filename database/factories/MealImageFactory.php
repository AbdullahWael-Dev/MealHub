<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\MealImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MealImageFactory extends Factory
{
    protected $model = MealImage::class;

    public function definition(): array
    {
        return [
            'meal_id' => Meal::factory(),
            'image_path' => $this->createLocalPlaceholderImage(),
            'alt_text' => fake()->words(3, true),
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }

    protected function createLocalPlaceholderImage(): string
    {
        $filename = 'meal-images/' . Str::uuid() . '.svg';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <rect width="800" height="600" fill="#F3F4F6"/>
  <rect x="40" y="40" width="720" height="520" rx="18" fill="#E5E7EB" stroke="#D1D5DB" stroke-width="4"/>
  <circle cx="400" cy="220" r="90" fill="#FDBA74"/>
  <path d="M280 430c20-70 90-110 120-110s100 40 120 110" fill="#F59E0B"/>
  <text x="400" y="520" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" fill="#374151">MealHub</text>
</svg>
SVG;

        Storage::disk('public')->put($filename, $svg);

        return $filename;
    }
}