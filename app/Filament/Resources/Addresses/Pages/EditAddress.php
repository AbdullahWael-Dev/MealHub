<?php

namespace App\Filament\Resources\Addresses\Pages;

use App\Exceptions\AddressHasOrdersException;
use App\Filament\Resources\Addresses\AddressResource;
use App\Models\Address;
use App\Services\V1\AddressServices\AddressService;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(AddressService::class)->update($record, $data);
        }
        catch (Exception $e) {
            Notification::make()
                ->title('Error updating address')
                ->body('An unexpected error occurred while updating the address.')
                ->danger()
                ->send();

            return $record;
        }
    }
}