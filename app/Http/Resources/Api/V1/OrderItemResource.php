<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource{
        
    public function toArray(Request $request): array
    {
          return [
            'id' => $this->id,
            'meal_id' => $this->meal_id,
            'meal_name' => $this->meal_name,
            'unit_price' => (float) $this->unit_price,
            'quantity' => $this->quantity,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
