<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'payment' => [
                'method' => $this->payment_method->value,
                'status' => $this->payment_status->value,
            ],
            'shipping_address' => [
                'name' => $this->shipping_name,
                'phone' => $this->shipping_phone,
                'city' => $this->shipping_city,
                'area' => $this->shipping_area,
                'street' => $this->shipping_street,
                'building' => $this->shipping_building,
                'floor' => $this->shipping_floor,
                'apartment' => $this->shipping_apartment,
                'landmark' => $this->shipping_landmark,
            ],

            'items' => $this->whenLoaded('items', fn() => $this->items->map(fn($item) => [
                'meal_id' => $item->meal_id,
                'meal_name' => $item->meal_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])),

          
            'summary' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount,
                'delivery_fee' => (float) $this->delivery_fee,
                'total' => (float) $this->total,
            ],
            'notes' => $this->notes,
            'timestamps' => [
                'created_at' => $this->created_at,
                'confirmed_at' => $this->confirmed_at,
                'preparing_at' => $this->preparing_at,
                'out_for_delivery_at' => $this->out_for_delivery_at,
                'delivered_at' => $this->delivered_at,
                'cancelled_at' => $this->cancelled_at,
            ],
        ];
    }
}
