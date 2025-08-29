<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de pacientes', Patient::count())
                ->description('Número total en el sistema')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Pacientes con cotizaciones', Patient::has('quotes')->count())
                ->description('Pacientes que tienen cotizaciones')
                ->descriptionIcon('heroicon-m-document-text')
                ->color(Color::Neutral),

            Stat::make('Pacientes con observaciones', Patient::has('observations')->count())
                ->description('Pacientes con observaciones médicas')
                ->descriptionIcon('heroicon-m-clipboard-document')
                ->color(Color::Neutral),

            Stat::make('Pacientes con alergias', Patient::whereNotNull('allergies')->where('allergies', '!=', '')->count())
                ->description('Pacientes que reportan alergias')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(Color::Neutral),
        ];
    }
}
