<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatusStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $cardClass = 'rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm hover:shadow-md transition-shadow';

        return [
            Stat::make('Pending', Order::where('status', OrderStatus::Pending)->count())
                ->descriptionIcon('heroicon-m-clock')
                ->description('Awaiting confirmation')
                ->color('gray')
                ->extraAttributes(['class' => $cardClass]),

            Stat::make('Out for Delivery', Order::where('status', OrderStatus::OutForDelivery)->count())
                ->descriptionIcon('heroicon-m-truck')
                ->description('On the road')
                ->color('primary')
                ->extraAttributes(['class' => $cardClass]),

            Stat::make('Delivered', Order::where('status', OrderStatus::Delivered)->count())
                ->descriptionIcon('heroicon-m-check-circle')
                ->description('Completed successfully')
                ->color('success')
                ->extraAttributes(['class' => $cardClass]),

            Stat::make('Cancelled', Order::where('status', OrderStatus::Cancelled)->count())
                ->descriptionIcon('heroicon-m-x-circle')
                ->description('Did not complete')
                ->color('danger')
                ->extraAttributes(['class' => $cardClass]),
        ];
    }
}