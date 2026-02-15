<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PatientsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $appTrend = Trend::model(Appointment::class)
            ->between(now()->subDays(6), now())
            ->perDay()
            ->count();
        $patientTrend = Trend::model(Patient::class)
            ->between(now()->subDays(6), now())
            ->perDay()
            ->count();

        return [
            Stat::make('Total Citas', Appointment::count())
                ->description('Tendencia semanal')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($appTrend->map(fn(TrendValue $v) => $v->aggregate)->toArray())
                ->color('success'),

            Stat::make('Citas Hoy', Appointment::whereDate('appointment_date', today())->count())
                ->description(now()->format('d/m/y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Pacientes', Patient::count())
                ->description('Total en base')
                ->descriptionIcon('heroicon-m-users')
                ->chart($patientTrend->map(fn(TrendValue $v) => $v->aggregate)->toArray())
                ->color('primary'),

            Stat::make('Nuevos Pacientes', Patient::whereMonth('created_at', now()->month)->count())
                ->description('Este mes')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
        ];
    }
}
