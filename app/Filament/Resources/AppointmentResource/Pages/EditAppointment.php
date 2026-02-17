<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

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
                    Forms\Components\TextInput::make('practice')
                        ->label('Nombre del consultorio')
                        ->required()
                        ->placeholder('Asistente Ejemplo'),
                ])
                ->action(function (Appointment $record, array $data) {
                    $apiKey = env('RETELL_API_KEY');
                    $agentId = 'agent_b38569dae01a294e4655094525';
                    $fromNumber = '+18456066291';

                    $toNumber = $record->chat_id;

                    try {
                        $response = Http::withToken($apiKey)
                            ->post('https://api.retellai.com/v2/create-phone-call', [
                                'from_number' => $fromNumber,
                                'to_number'   => $toNumber,
                                'override_agent_id' => $agentId,

                                'retell_llm_dynamic_variables' => [
                                    'nombre_paciente' => $record->patient_name,
                                    'fecha_cita_iso'      => $record->appointment_date,
                                    'hora_cita_iso'       => $record->appointment_time,
                                    'nombre_doctor'   => $data['doctor_name'],
                                    'nombre_consultorio' => $data['practice'],
                                ]
                            ]);

                        if ($response->successful()) {
                            $callData = $response->json();
                            Log::info('Llamada Retell Iniciada:', $callData);

                            Notification::make()
                                ->title('Llamada iniciada correctamente')
                                ->body('Call ID: ' . $callData['call_id'])
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Error al iniciar la llamada')
                            ->body('Código de error: ' . $response->body())
                            ->danger()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()->title('Error de conexión')->danger()->send();
                    }
                }),
        ];
    }
}
