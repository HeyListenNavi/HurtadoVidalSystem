<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AppointmentsByStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Citas';
    protected static ?string $description = 'Resumen de estados del flujo actual.';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Appointment::select('process_status', DB::raw('count(*) as total'))
            ->groupBy('process_status')
            ->pluck('total', 'process_status')
            ->toArray();

        $config = [
            'completed'   => ['label' => 'Completadas', 'color' => '#059669'],
            'in_progress' => ['label' => 'En Proceso',  'color' => '#10b981'],
            'cancelled'   => ['label' => 'Canceladas',  'color' => '#6ee7b7'],
            'rejected'    => ['label' => 'Rechazadas',  'color' => '#d1fae5'],
        ];

        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($config as $key => $settings) {
            $labels[] = $settings['label'];
            $counts[] = $data[$key] ?? 0;
            $colors[] = $settings['color'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 4
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'size' => 10,
                        ],
                        'color' => '#000',
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'bodyFont' => [
                        'size' => 10,
                    ],
                ],
            ],
            'cutout' => '75%',
        ];
    }
}
