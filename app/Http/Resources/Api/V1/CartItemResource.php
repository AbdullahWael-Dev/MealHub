<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $unitPrice = $this->meal->discount_price ?? $this->meal->price;
        $subtotal = $unitPrice * $this->quantity;
        return [
            'meal' => [
                'id' => $this->meal->id,
                'name' => $this->meal->name,
                'price' => $this->meal->price,
                'discount_price' => $this->meal->discount_price,
                'unit_price' => $unitPrice,
                'image' => $this->meal->primaryImage?->image_path
                    ? $this->meal->primaryImage->image_url
                    : null,
            ],
            'quantity' => $this->quantity,
            'subtotal' => $subtotal,
        ];
    }
}
