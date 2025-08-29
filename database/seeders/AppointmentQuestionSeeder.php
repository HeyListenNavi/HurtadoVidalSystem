<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentSetting;

class AppointmentQuestionSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
