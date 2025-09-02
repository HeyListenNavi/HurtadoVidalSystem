<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class EstimatedRevenuePerMonthWidget extends ChartWidget
{
    protected static ?string $heading = 'Ingresos Estimados por Mes';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Trend::query(Quote::query()->where('status', 'approved'))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear()
            )
            ->perMonth()
            ->sum('total_amount');

        return [
            'labels' => $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('M')),
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
