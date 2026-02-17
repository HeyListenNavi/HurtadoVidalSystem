<?php

namespace App\Filament\Resources\AppointmentResource\Widgets;

use App\Models\Appointment;
use App\Filament\Resources\AppointmentResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AppointmentCalendarWidget extends FullCalendarWidget
{
    protected static bool $isDiscovered = false;

    public Model | string | null $model = Appointment::class;

    public function config(): array
    {
        return [
            'locale' => 'es',             // Translate to Spanish
            'firstDay' => 1,              // Start week on Monday
            'initialView' => 'timeGridWeek',
            'slotDuration' => '00:15:00', // 15 min slots for clean alignment
            'slotLabelInterval' => '00:30',
            'allDaySlot' => false,
            'scrollTime' => '08:00:00',
            'nowIndicator' => true,
            'selectable' => true,
            'editable' => true,
            'snapDuration' => '00:15:00',

            // Buttons translation (Optional explicit overrides)
            'buttonText' => [
                'today' => 'Hoy',
                'month' => 'Mes',
                'week' => 'Semana',
                'day' => 'Día',
                'list' => 'Lista',
            ],

            'eventDidMount' => <<<JS
                function({ event, el }) {
                    el.setAttribute("x-tooltip", "tooltip");
                    el.setAttribute("data-tippy-content", event.extendedProps.time + ' - ' + event.title);
                }
            JS,
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Appointment::query()
            ->where('appointment_date', '>=', $fetchInfo['start'])
            ->where('appointment_date', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (Appointment $appointment) {
                $start = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time);
                $end = $start->copy()->addMinutes(30);

                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient_name,
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                    'url' => AppointmentResource::getUrl('edit', ['record' => $appointment]),
                    'color' => match ($appointment->process_status) {
                        'completed' => '#10b981',
                        'rejected' => '#ef4444',
                        'cancelled' => '#f59e0b',
                        default => '#3b82f6',
                    },
                    'extendedProps' => [
                        'description' => ucfirst($appointment->process_status),
                        'time' => $start->format('H:i'),
                    ]
                ];
            })
            ->toArray();
    }

    /**
     * KEY FIX HERE: added ->mountUsing()
     */
    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->label('Agendar Cita')
                ->modalHeading('Nueva Cita')
                ->form(fn(Form $form) => AppointmentResource::form($form))
                // 1. This tells the Action: "When you open, take the arguments and fill the form"
                ->mountUsing(function (Form $form, array $arguments) {
                    $form->fill([
                        'appointment_date' => $arguments['appointment_date'] ?? null,
                        'appointment_time' => $arguments['appointment_time'] ?? null,
                        'process_status' => 'in_progress', // Default status
                    ]);
                }),
        ];
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        if ($allDay) {
            $date = Carbon::parse($start)->toDateString();
            $time = '09:00:00';
        } else {
            $carbon = Carbon::parse($start);
            $date = $carbon->toDateString();
            $time = $carbon->toTimeString();
        }

        // 2. We pass these values as "arguments" to the action
        $this->mountAction('create', [
            'appointment_date' => $date,
            'appointment_time' => $time,
        ]);
    }
}
