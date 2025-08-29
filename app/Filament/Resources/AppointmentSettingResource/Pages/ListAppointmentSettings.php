<?php

namespace App\Filament\Resources\AppointmentSettingResource\Pages;

use App\Filament\Resources\AppointmentSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppointmentSettings extends ListRecords
{
    protected static string $resource = AppointmentSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
