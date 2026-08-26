<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->formatStateUsing(fn($state) => "#{$state}"),

                TextColumn::make('user.name')
                    ->label('Customer'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(OrderStatus $state) => $state->label())
                    ->color(fn(OrderStatus $state) => match ($state) {
                        OrderStatus::Pending => 'gray',
                        OrderStatus::Confirmed => 'info',
                        OrderStatus::Preparing => 'warning',
                        OrderStatus::OutForDelivery => 'primary',
                        OrderStatus::Delivered => 'success',
                        OrderStatus::Cancelled => 'danger',
                    }),

                TextColumn::make('total')
                    ->money('EGP'),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since(),
            ])->striped();
    }
}
