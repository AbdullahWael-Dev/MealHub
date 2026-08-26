<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $meal = $this->resource['meal'];

        return [
            'reason' => $this->resource['reason'],
            'meal' => [
                'id' => $meal->id,
                'name' => $meal->name,
                'description' => $meal->description,
                'price' => $meal->price,
                'discount_price' => $meal->discount_price,
                'effective_price' => $meal->discount_price ?? $meal->price,
                'rating' => $meal->avg_rating,
                'reviews_count' => $meal->review_count,
                'image' => $meal->display_image?->image_url ?? null,
                'category' => $meal->category?->name,
            ],
        ];
    }
}