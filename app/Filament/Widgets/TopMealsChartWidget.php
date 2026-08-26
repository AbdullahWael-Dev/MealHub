<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;

class TopMealsChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Top 5 Best-Selling Meals';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $topMeals = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->selectRaw('order_items.meal_name, SUM(order_items.quantity) as total_qty')
            ->groupBy('order_items.meal_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Units Sold',
                    'data' => $topMeals->pluck('total_qty')->toArray(),
                    'backgroundColor' => ['#f97316', '#fb923c', '#fdba74', '#fed7aa', '#ffedd5'],
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                    'maxBarThickness' => 60,
                ],
            ],
            'labels' => $topMeals->pluck('meal_name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'backgroundColor' => 'rgba(17, 24, 39, 0.9)',
                    'padding' => 12,
                    'cornerRadius' => 8,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.15)'],
                    'ticks' => ['color' => 'rgba(107, 114, 128, 0.8)', 'stepSize' => 1],
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['color' => 'rgba(107, 114, 128, 0.8)'],
                ],
            ],
        ];
    }
}
