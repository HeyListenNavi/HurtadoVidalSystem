<?php

namespace App\Filament\Resources\AppointmentResponseResource\Pages;

use App\Filament\Resources\AppointmentResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppointmentResponses extends ListRecords
{
    protected static string $resource = AppointmentResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
