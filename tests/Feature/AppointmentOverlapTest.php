<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AppointmentOverlapTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cannot_reschedule_to_an_occupied_slot_exact_match()
    {
        // given an existing appointment from 14:00 to 15:00
        Appointment::factory()->create([
            'appointment_date' => '2026-03-30',
            'appointment_time' => '14:00:00',
            'process_status' => 'completed',
        ]);
        $myAppointment = Appointment::factory()->create([
            'chat_id' => '1234567890',
            'appointment_date' => '2026-03-30',
            'appointment_time' => '10:00',
            'process_status' => 'in_progress',
        ]);

        // when rescheduling another appointment for the exact time and date of an existing appointment
        $response = $this->postJson("/api/bot/appointments/{$myAppointment->chat_id}/reschedule", [
            'new_date' => '2026-03-30',
            'new_time' => '14:00',
        ]);

        // then the response status should be 422 and the appointment time should remain the same
        $response->assertStatus(422);
        $this->assertDatabaseHas('appointments', [
            'id' => $myAppointment->id,
            'appointment_time' => '10:00',
        ]);
    }

    #[Test]
    public function cannot_reschedule_to_a_partial_overlap_start_inside()
    {
        // given an existing appointment from 14:00 to 15:00
        Appointment::factory()->create([
            'appointment_date' => '2026-03-30',
            'appointment_time' => '14:00:00',
            'process_status' => 'completed',
        ]);
        $myAppointment = Appointment::factory()->create([
            'chat_id' => '1234567890',
            'appointment_date' => '2026-03-30',
            'appointment_time' => '10:00',
            'process_status' => 'in_progress'
        ]);

        // when rescheduling for 14:30
        $response = $this->postJson("/api/bot/appointments/{$myAppointment->chat_id}/reschedule", [
            'new_date' => '2026-03-30',
            'new_time' => '14:30',
        ]);

        // then the response status should be 422 and the appointment time should remain the same
        $response->assertStatus(422);
        $this->assertDatabaseHas('appointments', [
            'id' => $myAppointment->id,
            'appointment_time' => '10:00',
        ]);
    }

    #[Test]
    public function cannot_reschedule_to_a_partial_overlap_end_inside()
    {
        // given an existing appointment from 14:00 to 15:00
        Appointment::factory()->create([
            'appointment_date' => '2026-03-30',
            'appointment_time' => '14:00:00',
            'process_status' => 'completed',
        ]);
        $myAppointment = Appointment::factory()->create([
            'chat_id' => '1234567890',
            'appointment_date' => '2026-03-30',
            'appointment_time' => '10:00',
            'process_status' => 'in_progress'
        ]);

        // when rescheduling for 13:30
        $response = $this->postJson("/api/bot/appointments/{$myAppointment->chat_id}/reschedule", [
            'new_date' => '2026-03-30',
            'new_time' => '13:30',
        ]);

        // then the response status should be 422 and the appointment time should remain the same
        $response->assertStatus(422);
        $this->assertDatabaseHas('appointments', [
            'id' => $myAppointment->id,
            'appointment_time' => '10:00',
        ]);
    }

    #[Test]
    public function adjacent_appointments_are_allowed()
    {
        // given an existing appointment ending at 15:00
        Appointment::factory()->create([
            'appointment_date' => '2026-03-30',
            'appointment_time' => '14:00:00',
            'process_status' => 'completed',
        ]);
        $myAppointment = Appointment::factory()->create([
            'chat_id' => '1234567890',
            'process_status' => 'in_progress'
        ]);

        // when rescheduling for exactly 15:00
        $response = $this->postJson("/api/bot/appointments/{$myAppointment->chat_id}/reschedule", [
            'new_date' => '2026-03-30',
            'new_time' => '15:00',
        ]);

        // then the response status should be 200 and the appointment time should be updated
        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id' => $myAppointment->id,
            'appointment_time' => '15:00',
        ]);
    }

    #[Test]
    public function availability_endpoint_detects_overlap()
    {
        // given an existing appointment at 16:00
        Appointment::factory()->create([
            'appointment_date' => '2026-03-30',
            'appointment_time' => '16:00:00',
            'process_status' => 'completed',
        ]);

        // when checking availability for 16:30
        $response = $this->getJson("/api/bot/appointments/check-availability?date=2026-03-30&time=16:30");

        // then it should return available as false
        $response->assertStatus(200)
                 ->assertJson(['available' => false]);
    }

    #[Test]
    public function availability_endpoint_returns_true_when_free()
    {
        // given no appointments at that time

        // when checking availability for a free slot
        $response = $this->getJson("/api/bot/appointments/check-availability?date=2026-03-30&time=09:00");

        // then it should return available as true
        $response->assertStatus(200)
                 ->assertJson(['available' => true]);
    }

    #[Test]
    public function availability_endpoint_ignores_cancelled_appointments()
    {
        // given an existing cancelled appointment at 10:00
        Appointment::factory()->create([
            'appointment_date' => '2026-03-30',
            'appointment_time' => '10:00:00',
            'process_status' => 'cancelled',
        ]);

        // when checking availability for that same time
        $response = $this->getJson("/api/bot/appointments/check-availability?date=2026-03-30&time=10:00");

        // then it should return available as true
        $response->assertStatus(200)
                 ->assertJson(['available' => true]);
    }
}
