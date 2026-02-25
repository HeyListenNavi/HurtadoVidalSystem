<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AppointmentCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Gestión Clínica';
    protected static ?string $title = 'Calendario de Citas';

    protected static string $view = 'filament.pages.appointment-calendar';
}
