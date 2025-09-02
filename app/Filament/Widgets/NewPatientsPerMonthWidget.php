<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class NewPatientsPerMonthWidget extends ChartWidget
{
    protected static ?string $heading = 'Pacientes Nuevos por Mes';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $data = Trend::model(Patient::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear()
            )
            ->perMonth()
            ->count();

        return [
            'labels' => $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('M')),
            'datasets' => [
                [
                    'label' => 'Nuevos Pacientes',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
        ];
    }
    protected function getType(): string
    {
        return 'bar';
    }
}
