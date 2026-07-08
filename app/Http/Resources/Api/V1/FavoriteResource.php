<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
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
            'meal' => [
                'id' => $this->meal->id,
                'name' => $this->meal->name,
                'slug' => $this->meal->slug,
                'price' => $this->meal->price,
                'final_price' => $this->meal->final_price,
                'has_discount' => $this->meal->has_discount,
                'display_image' => $this->meal->display_image?->imageUrl,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
