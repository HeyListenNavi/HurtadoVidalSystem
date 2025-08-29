<?php

namespace App\Filament\Resources\AppointmentQuestionResource\Pages;

use App\Filament\Resources\AppointmentQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointmentQuestion extends EditRecord
{
    protected static string $resource = AppointmentQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
