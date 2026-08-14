<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order #')
                            ->formatStateUsing(fn ($state) => "#{$state}"),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                            ->color(fn (OrderStatus $state) => match ($state) {
                                OrderStatus::Pending => 'gray',
                                OrderStatus::Confirmed => 'info',
                                OrderStatus::Preparing => 'warning',
                                OrderStatus::OutForDelivery => 'primary',
                                OrderStatus::Delivered => 'success',
                                OrderStatus::Cancelled => 'danger',
                            }),

                        TextEntry::make('created_at')->dateTime(),
                    ]),

                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('Name'),
                        TextEntry::make('user.phone')->label('Phone'),
                    ]),

                Section::make('Shipping Address')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('shipping_name')->label('Recipient'),
                        TextEntry::make('shipping_phone')->label('Phone'),
                        TextEntry::make('shipping_city')->label('City'),
                        TextEntry::make('shipping_area')->label('Area'),
                        TextEntry::make('shipping_street')->label('Street'),
                        TextEntry::make('shipping_building')->label('Building'),
                        TextEntry::make('shipping_floor')->label('Floor')->placeholder('—'),
                        TextEntry::make('shipping_apartment')->label('Apartment')->placeholder('—'),
                        TextEntry::make('shipping_landmark')->label('Landmark')->placeholder('—'),
                    ]),

                Section::make('Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('meal_name')->label('Meal'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Unit Price')->money('EGP'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('EGP'),
                            ]),
                    ]),

                Section::make('Payment')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('payment_method')
                            ->badge()
                            ->formatStateUsing(fn (PaymentMethod $state) => $state->label()),

                        TextEntry::make('payment_status')
                            ->badge()
                            ->formatStateUsing(fn (PaymentStatus $state) => $state->label())
                            ->color(fn (PaymentStatus $state) => match ($state) {
                                PaymentStatus::Pending => 'gray',
                                PaymentStatus::Paid => 'success',
                                PaymentStatus::Failed => 'danger',
                                PaymentStatus::Refunded => 'warning',
                            }),
                    ]),

                Section::make('Order Summary')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('subtotal')->money('EGP'),
                        TextEntry::make('discount')->money('EGP'),
                        TextEntry::make('delivery_fee')->money('EGP'),
                        TextEntry::make('total')->money('EGP')->weight('bold'),
                    ]),

                Section::make('Status Timeline')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('confirmed_at')->dateTime()->placeholder('—'),
                        TextEntry::make('preparing_at')->dateTime()->placeholder('—'),
                        TextEntry::make('out_for_delivery_at')->dateTime()->placeholder('—'),
                        TextEntry::make('delivered_at')->dateTime()->placeholder('—'),
                        TextEntry::make('cancelled_at')->dateTime()->placeholder('—'),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')->hiddenLabel()->placeholder('No notes.'),
                    ])
                    ->visible(fn ($record) => filled($record->notes)),
            ]);
    }
}