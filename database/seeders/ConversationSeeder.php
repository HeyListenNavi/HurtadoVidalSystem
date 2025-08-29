<?php

namespace Database\Seeders;

use App\Models\AppointmentQuestion;
use App\Models\AppointmentSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Conversation;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::all();
        foreach ($appointments as $appointment) {
            Conversation::factory()->create([
                'chat_id' => $appointment->chat_id,
                'user_name' => $appointment->patient_name,
                'processable_id' => $appointment->id,
                'processable_type' => \App\Models\Appointment::class,
            ]);
        }
    }
}
