<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AppointmentsPerDayWidget extends ChartWidget
{
    protected static ?string $heading = 'Citas por Día';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(Appointment::class)
            ->between(
                start: now()->subDays(6)->startOfDay(),
                end: now()->endOfDay()
            )
            ->perDay()
            ->count();

        return [
            'labels' => $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('d/m')),
            'datasets' => [
                [
                    'label' => 'Citas',
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
