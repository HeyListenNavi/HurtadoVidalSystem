<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Quote;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Citas', Appointment::count())
                ->description('Total de citas registradas')
                ->descriptionIcon('heroicon-m-calendar-days'),

            Stat::make('Citas de Hoy', Appointment::whereDate('appointment_date', today())->count())
                ->description('Citas agendadas para ' . now()->format('d/m/Y'))
                ->descriptionIcon('heroicon-o-calendar-days'),

            Stat::make('Pacientes', Patient::count())
                ->description('Pacientes en el sistema')
                ->descriptionIcon('heroicon-m-user-group'),

            Stat::make('Nuevos Pacientes', Patient::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count())
                ->description('Registrados en ' . now()->locale('es')->monthName)
                ->descriptionIcon('heroicon-o-user-group'),

            Stat::make('Cotizaciones', Quote::count())
                ->description('Cotizaciones generadas')
                ->descriptionIcon('heroicon-m-document-text'),

            Stat::make('Productos', Product::count())
                ->description('Productos disponibles')
                ->descriptionIcon('heroicon-m-cube'),
        ];
    }
}
