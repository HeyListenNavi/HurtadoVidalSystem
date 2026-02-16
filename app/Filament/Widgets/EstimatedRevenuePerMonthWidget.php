<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class EstimatedRevenuePerMonthWidget extends ChartWidget
{
    protected static ?string $heading = 'Ingresos Proyectados';
    protected static ?string $description = 'Suma de presupuestos aprobados.';
    protected static ?int $sort = 3;

    public ?string $filter = '365';

    protected function getFilters(): ?array
    {
        return [
            '180' => '6 Meses',
            '365' => 'Este Año',
            '730' => '2 Años',
        ];
    }

    protected function getData(): array
    {
        $filterValue = (int) $this->filter;

        $data = Trend::query(Quote::query()->where('status', 'approved'))
            ->between(
                start: now()->subDays($filterValue - 1)->startOfMonth(),
                end: now()->endOfMonth()
            )
            ->perMonth()
            ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#ffffff',
                    'pointBorderColor' => '#10b981',
                    'pointRadius' => 3,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M y')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'font' => ['size' => 10],
                        'color' => '#9ca3af',
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 10],
                        'color' => '#9ca3af',
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
