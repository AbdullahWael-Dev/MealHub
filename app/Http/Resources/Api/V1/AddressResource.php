<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'recipient_name' => $this->recipient_name,
            'phone'          => $this->phone,
            'city'           => $this->city,
            'area'           => $this->area,
            'street'         => $this->street,
            'building'       => $this->building,
            'floor'          => $this->floor,
            'apartment'      => $this->apartment,
            'landmark'       => $this->landmark,
            'notes'          => $this->notes,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'is_default'     => $this->is_default,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
