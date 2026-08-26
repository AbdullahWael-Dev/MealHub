<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    public function getHeading(): string
    {
        return 'Revenue Over Time';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '12m' => 'Last 12 months',
        ];
    }

    protected function getDefaultFilter(): ?string
    {
        return '30';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        return $this->filter === '12m'
            ? $this->buildMonthly()
            : $this->buildDaily((int) $this->filter);
    }

    private function buildDaily(int $days): array
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $revenues = Order::query()
            ->where('status', OrderStatus::Delivered)
            ->where('delivered_at', '>=', $start)
            ->selectRaw('DATE(delivered_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->format('d M');
            $data[] = (float) ($revenues[$date->toDateString()] ?? 0);
        }

        return $this->chartPayload($labels, $data);
    }

    private function buildMonthly(): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();

        $revenues = Order::query()
            ->where('status', OrderStatus::Delivered)
            ->where('delivered_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(delivered_at, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {
            $date = $start->copy()->addMonths($i);
            $labels[] = $date->format('M Y');
            $data[] = (float) ($revenues[$date->format('Y-m')] ?? 0);
        }

        return $this->chartPayload($labels, $data);
    }

    private function chartPayload(array $labels, array $data): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Revenue (EGP)',
                    'data' => $data,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#22c55e',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
                    'titleFont' => ['weight' => 'bold'],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.15)'],
                    'ticks' => ['color' => 'rgba(107, 114, 128, 0.8)'],
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['color' => 'rgba(107, 114, 128, 0.8)'],
                ],
            ],
            'elements' => [
                'point' => ['radius' => 3, 'hoverRadius' => 6],
                'line' => ['borderWidth' => 2.5],
            ],
        ];
    }
}
