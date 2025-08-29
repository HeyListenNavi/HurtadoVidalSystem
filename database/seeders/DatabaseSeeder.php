<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Appointment;
use App\Models\AppointmentSetting;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentResponse;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Creamos una configuración de cita
        $setting = AppointmentSetting::factory()->create([
            'name' => 'General Appointment',
            'rejection_reason' => 'No se cumplen los criterios de la cita.',
        ]);

        // Creamos preguntas asociadas a la configuración
        $question1 = $setting->questions()->create([
            'question_text' => '¿Cuál es su nombre completo?',
            'approval_criteria' => ['type' => 'text', 'min_length' => 5],
            'order' => 1,
        ]);

        $question2 = $setting->questions()->create([
            'question_text' => '¿Cuál es el motivo de su visita?',
            'approval_criteria' => ['type' => 'text', 'min_length' => 10],
            'order' => 2,
        ]);

        $question3 = $setting->questions()->create([
            'question_text' => '¿Qué día desea agendar su cita? (Formato: AAAA-MM-DD)',
            'approval_criteria' => ['type' => 'date', 'min_length' => 10],
            'order' => 3,
        ]);

        // Creamos una cita de ejemplo
        $appointment = Appointment::factory()->create([
            'current_question_id' => $question1->id,
            'process_status' => 'in_progress',
        ]);

        // Creamos la conversación asociada a la cita
        Conversation::factory()->create([
            'chat_id' => $appointment->chat_id,
            'user_name' => $appointment->patient_name,
            'current_process' => 'App\Models\Appointment',
            'process_id' => $appointment->id,
        ]);

        // Creamos una respuesta de ejemplo
        AppointmentResponse::factory()->create([
            'appointment_id' => $appointment->id,
            'question_id' => $question1->id,
            'question_text_snapshot' => $question1->question_text,
            'user_response' => 'Carlos Pérez',
        ]);

        // Crear 10 conversaciones de prueba.
        Conversation::factory(10)->create()->each(function ($conversation) {
            // Para cada conversación, crear 5 mensajes asociados.
            $conversation->messages()->saveMany(
                Message::factory(5)->make([
                    'chat_id' => $conversation->chat_id,
                ])
            );
        });

        // Crear 50 citas para simular un volumen de datos.
        Appointment::factory(50)->create();
    }
}
