<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class AppointmentsPerDayWidget extends ChartWidget
{
    protected static ?string $heading = 'Actividad de Citas';
    protected static ?string $description = 'Volumen de citas agendadas.';
    protected static ?int $sort = 2;

    public ?string $filter = '7';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Días',
            '30' => 'Mes (Semanales)',
            '60' => '2 Meses (Semanales)',
            '180' => '6 Meses (Mensuales)',
            '365' => '1 Año (Mensuales)',
        ];
    }

    protected function getData(): array
    {
        $filterValue = (int) $this->filter;

        $trend = Trend::model(Appointment::class)->between(
            start: now()
                ->subDays($filterValue - 1)
                ->startOfDay(),
            end: now()->endOfDay(),
        );

        if ($filterValue >= 180) {
            $data = $trend->perMonth()->count();
            $labels = $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M y'));
        } elseif ($filterValue > 7) {
            $data = $trend->perWeek()->count();
            $labels = $data->map(function (TrendValue $value) {
                $parts = explode('-', $value->date);
                return 'S' . ($parts[1] ?? $value->date);
            });
        } else {
            $data = $trend->perDay()->count();
            $labels = $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('d/m'));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                    'pointBackgroundColor' => '#ffffff',
                    'pointBorderColor' => '#10b981',
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => '#10b981',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
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
