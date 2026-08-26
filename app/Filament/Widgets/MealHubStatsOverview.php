<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MealHubStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = Order::query()
            ->where('status', OrderStatus::Delivered)
            ->sum('total');

        return [
            Stat::make('Total Revenue', number_format((float) $totalRevenue, 2).' EGP')
                ->description('From delivered orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->trend(fn ($from) => Order::where('status', OrderStatus::Delivered)
                    ->whereDate('delivered_at', $from)
                    ->sum('total')))
                ->chartColor('success')
                ->color('success')
                ->extraAttributes(['class' => 'rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm hover:shadow-md transition-shadow']),

            Stat::make('Total Orders', Order::count())
                ->description('All orders, all statuses')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($this->trend(fn ($from) => Order::whereDate('created_at', $from)->count()))
                ->chartColor('info')
                ->color('info')
                ->extraAttributes(['class' => 'rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm hover:shadow-md transition-shadow']),

            Stat::make('Total Customers', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart($this->trend(fn ($from) => User::whereDate('created_at', $from)->count()))
                ->chartColor('warning')
                ->color('warning')
                ->extraAttributes(['class' => 'rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm hover:shadow-md transition-shadow']),

            Stat::make('Total Meals', Meal::count())
                ->description(Meal::where('is_available', true)->count().' currently available')
                ->descriptionIcon('heroicon-m-cake')
                ->color('danger')
                ->extraAttributes(['class' => 'rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm hover:shadow-md transition-shadow']),
        ];
    }

    private function trend(callable $valueForDate): array
    {
        $points = [];

        for ($i = 6; $i >= 0; $i--) {
            $points[] = (float) $valueForDate(Carbon::now()->subDays($i)->toDateString());
        }

        return $points;
    }
}