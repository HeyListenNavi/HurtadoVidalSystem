<?php

namespace App\Filament\Resources\AppointmentQuestionResource\Pages;

use App\Filament\Resources\AppointmentQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppointmentQuestions extends ListRecords
{
    protected static string $resource = AppointmentQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
