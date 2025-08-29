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
        $this->call([
            UserSeeder::class,
            PatientSeeder::class,
            PatientObservationSeeder::class,
            ProductSeeder::class,
            QuoteSeeder::class,
            AppointmentQuestionSeeder::class,
            AppointmentSeeder::class,
            AppointmentResponseSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
