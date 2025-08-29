<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TotalPatientsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Total de Pacientes por Año';

    protected function getData(): array
    {
        $data = Trend::model(Patient::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perYear()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Total de pacientes',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
