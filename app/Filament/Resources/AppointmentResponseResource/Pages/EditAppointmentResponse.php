<?php

namespace App\Filament\Resources\AppointmentResponseResource\Pages;

use App\Filament\Resources\AppointmentResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointmentResponse extends EditRecord
{
    protected static string $resource = AppointmentResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
