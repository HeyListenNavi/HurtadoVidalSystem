<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Quote;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        Patient::all()->each(function ($patient) {
            Quote::factory()
                ->count(2)
                ->create([
                    'patient_id' => $patient->id,
                ]);
        });
    }
}


