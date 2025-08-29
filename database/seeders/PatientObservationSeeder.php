<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PatientObservation;

class PatientObservationSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        foreach ($patients as $patient) {
            PatientObservation::factory(2)->create([
                'patient_id' => $patient->id,
            ]);
        }
    }
}
