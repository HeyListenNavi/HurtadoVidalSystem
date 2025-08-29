<?php

namespace App\Filament\Resources\AppointmentSettingResource\Pages;

use App\Filament\Resources\AppointmentSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointmentSetting extends EditRecord
{
    protected static string $resource = AppointmentSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
