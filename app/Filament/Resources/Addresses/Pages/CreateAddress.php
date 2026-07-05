<?php

namespace App\Filament\Resources\Addresses\Pages;

use App\Filament\Resources\Addresses\AddressResource;
use App\Models\Address;
use App\Models\User;
use App\Services\V1\AddressServices\AddressService;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;

    protected function handleRecordCreation(array $data): Address
    {
        $user = User::find($data['user_id']);
        unset($data['user_id']);
        return app(AddressService::class)->create($user, $data);
    }
}
