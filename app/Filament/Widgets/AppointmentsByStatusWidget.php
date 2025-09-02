<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class AppointmentsByStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Citas por Estado';

    protected static ?string $maxHeight = '240px';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $statuses = ['in_progress', 'completed', 'rejected', 'cancelled'];
        $labels = ['En Progreso', 'Completadas', 'Rechazadas', 'Canceladas'];
        $colors = ['#916F52', '#A98D79', '#C1AA99', '#D9C6B8'];

        $counts = [];
        foreach ($statuses as $status) {
            $counts[] = Appointment::where('process_status', $status)->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Appointment Status',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
