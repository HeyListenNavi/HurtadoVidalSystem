<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentResponse;

class AppointmentResponseSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::all();
        $questions = AppointmentQuestion::all();

        foreach ($appointments as $appointment) {
            foreach ($questions->random(3) as $question) {
                AppointmentResponse::factory()->create([
                    'appointment_id' => $appointment->id,
                    'question_id' => $question->id,
                    'question_text_snapshot' => $question->question_text,
                ]);
            }
        }
    }
}
