<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'final_price' => $this->final_price,
            'stock_quantity' => $this->stock_quantity,
            'is_available' => $this->is_available,
            'is_featured' => $this->is_featured,
            'avg_rating' => $this->avg_rating,
            'review_count' => $this->review_count,
            'preparation_time' => $this->preparation_time,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'images' => $this->images->map(fn($img) => [
                'id' => $img->id,
                'url' => $img->image_url,
                'is_primary' => $img->is_primary,
            ]),
        ];
    }
}
