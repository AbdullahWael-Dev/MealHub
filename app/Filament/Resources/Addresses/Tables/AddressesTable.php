<?php

namespace App\Filament\Resources\Addresses\Tables;

use App\Models\Address;
use App\Services\V1\AddressServices\AddressService;
use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class AddressesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width('1%'),

                TextColumn::make('title')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Home' => 'success',
                        'Work' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'Home' => 'Home',
                        'Work' => 'Work',
                        default => 'Other',
                    }),

                TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Phone number copied')
                    ->searchable(),

                TextColumn::make('full_location')
                    ->label('Address')
                    ->getStateUsing(fn (Address $record) => "{$record->city} - {$record->area}, {$record->street}")
                    ->description(fn (Address $record) => trim(
                        collect([
                            $record->building ? "Building {$record->building}" : null,
                            $record->floor ? "Floor {$record->floor}" : null,
                            $record->apartment ? "Apartment {$record->apartment}" : null,
                        ])->filter()->implode(' - ')
                    ))
                    ->wrap()
                    ->searchable(['city', 'area', 'street']),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter(),

            

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->since()
                    ->dateTimeTooltip('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('user.name')
                    ->label('User')
                    ->collapsible(),
            ])
            ->defaultGroup('user.name')
            ->filters([
                SelectFilter::make('title')
                    ->label('Address Type')
                    ->options([
                        'Home' => 'Home',
                        'Work' => 'Work',
                        'Other' => 'Other',
                    ]),

                TernaryFilter::make('is_default')
                    ->label('Default Address')
                    ->boolean()
                    ->trueLabel('Default Only')
                    ->falseLabel('Non-default Only')
                    ->native(false),

                SelectFilter::make('city')
                    ->label('City')
                    ->options(
                        fn () => Address::query()
                            ->distinct()
                            ->pluck('city', 'city')
                            ->toArray()
                    )
                    ->searchable(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Address $record) => ! $record->trashed()),

                DeleteAction::make()
                    ->visible(fn (Address $record) => ! $record->trashed())
                    ->requiresConfirmation()
                    ->action(function (Address $record, DeleteAction $action) {
                        try {
                            app(AddressService::class)->delete($record);
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete this address')
                                ->body($e->getMessage())
                                ->send();

                            $action->cancel();
                        }
                    }),

                RestoreAction::make()
                    ->visible(fn (Address $record) => $record->trashed()),

                ForceDeleteAction::make()
                    ->visible(fn (Address $record) => $record->trashed())
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $service = app(AddressService::class);
                            foreach ($records as $record) {
                                $service->delete($record);
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('No addresses found')
            ->emptyStateDescription('No addresses have been added yet.')
            ->emptyStateIcon('heroicon-o-map-pin')
            ->striped();
    }
}