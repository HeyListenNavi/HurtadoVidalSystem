<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('notify_appointment')
                ->label('Avisar Cita')
                ->icon('heroicon-o-bell-alert')
                ->color('info')
                ->form([
                    Forms\Components\TextInput::make('doctor_name')
                        ->label('Nombre del Doctor')
                        ->required()
                        ->placeholder('Dr. Ejemplo'),
                    Forms\Components\TextInput::make('assistant_name')
                        ->label('Nombre de la Asistente')
                        ->required()
                        ->placeholder('Asistente Ejemplo'),
                ])
                ->action(function (Appointment $record, array $data) {
                    $apiPayload = [
                        'paciente' => $record->patient_name,
                        'telefono' => $record->chat_id,
                        'fecha_cita' => $record->appointment_date,
                        'hora_cita' => $record->appointment_time,
                        'doctor_asignado' => $data['doctor_name'],
                        'asistente_asignada' => $data['assistant_name'],
                    ];

                    Log::info('LLAMADA API AVISO CITA:', $apiPayload);

                    Notification::make()
                        ->title('Aviso enviado correctamente')
                        ->success()
                        ->send();
                }),
        ];
    }
}
