<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    public function getHeading(): string
    {
        return 'Orders by Status';
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $colorMap = [
            'pending' => '#9ca3af',
            'confirmed' => '#38bdf8',
            'preparing' => '#f59e0b',
            'out_for_delivery' => '#6366f1',
            'delivered' => '#22c55e',
            'cancelled' => '#ef4444',
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach (OrderStatus::cases() as $status) {
            $labels[] = $status->label();
            $data[] = (int) ($counts[$status->value] ?? 0);
            $colors[] = $colorMap[$status->value];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 16,
                        'usePointStyle' => true,
                        'font' => ['size' => 12],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(17, 24, 39, 0.9)',
                    'padding' => 12,
                    'cornerRadius' => 8,
                ],
            ],
        ];
    }
}
