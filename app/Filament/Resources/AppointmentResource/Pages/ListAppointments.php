<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-o-list-bullet'),

            'en_progreso' => Tab::make('En Progreso')
                ->icon('heroicon-o-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'in_progress')),

            'completadas' => Tab::make('Completadas')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'completed')),

            'rechazadas' => Tab::make('Rechazadas')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'rejected')),

            'canceladas' => Tab::make('Canceladas')
                //->icon('heroicon-o-ban')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'cancelled')),
        ];
    }
}