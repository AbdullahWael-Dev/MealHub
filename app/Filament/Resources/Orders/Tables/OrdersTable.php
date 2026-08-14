<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Actions\OrderStatusActions;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
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

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->formatStateUsing(fn (PaymentMethod $state) => $state->label()),

                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state) => $state->label())
                    ->color(fn (PaymentStatus $state) => match ($state) {
                        PaymentStatus::Pending => 'gray',
                        PaymentStatus::Paid => 'success',
                        PaymentStatus::Failed => 'danger',
                        PaymentStatus::Refunded => 'warning',
                    }),

                TextColumn::make('subtotal')->money('EGP')->sortable(),
                TextColumn::make('discount')->money('EGP')->sortable(),
                TextColumn::make('delivery_fee')->money('EGP')->sortable(),
                TextColumn::make('total')->money('EGP')->sortable()->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])),

                SelectFilter::make('payment_status')
                    ->options(collect(PaymentStatus::cases())
                        ->mapWithKeys(fn($cases) => [$cases->value => $cases->label()])),

                SelectFilter::make('payment_method')
                    ->options(collect(PaymentMethod::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                ...OrderStatusActions::all(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}