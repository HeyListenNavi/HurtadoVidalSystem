<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class NewPatientsPerMonthWidget extends ChartWidget
{
    protected static ?string $heading = 'Crecimiento de Pacientes';
    protected static ?string $description = 'Nuevos registros en el sistema.';
    protected static ?int $sort = 5;

    public ?string $filter = '365';

    protected function getFilters(): ?array
    {
        return [
            '30' => 'Este Mes (Sem)',
            '60' => '2 Meses (Sem)',
            '180' => '6 Meses (Mes)',
            '365' => 'Este Año (Mes)',
        ];
    }

    protected function getData(): array
    {
        $filterValue = (int) $this->filter;

        $trend = Trend::model(Patient::class)
            ->between(
                start: now()->subDays($filterValue - 1)->startOfDay(),
                end: now()->endOfDay()
            );

        if ($filterValue >= 180) {
            $data = $trend->perMonth()->count();
            $labels = $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M y'));
        } else {
            $data = $trend->perWeek()->count();
            $labels = $data->map(function (TrendValue $value) {
                $parts = explode('-', $value->date);
                return 'S' . ($parts[1] ?? $value->date);
            });
        }

       return [
            'datasets' => [
                [
                    'label' => 'Pacientes Nuevos',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => '#059669',
                    'hoverBorderColor' => '#059669',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                        'drawBorder' => false,
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
