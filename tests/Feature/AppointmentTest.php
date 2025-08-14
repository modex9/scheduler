<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_appointment(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $appointment = Appointment::create([
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
            'status' => 'confirmed',
            'notes' => 'First time client',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
            'status' => 'confirmed',
            'notes' => 'First time client',
        ]);
    }

    public function test_can_access_service_relationship(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $appointment = Appointment::create([
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        $this->assertEquals('Haircut', $appointment->service->name);
        $this->assertEquals(60, $appointment->service->duration_minutes);
    }

    public function test_confirmed_scope_returns_only_confirmed_appointments(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        Appointment::create([
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'appointment_date' => '2024-01-16',
            'appointment_time' => '15:00',
            'service_id' => $service->id,
            'client_email' => 'jane@example.com',
            'client_name' => 'Jane Doe',
            'status' => 'cancelled',
        ]);

        $confirmedAppointments = Appointment::confirmed()->get();

        $this->assertCount(1, $confirmedAppointments);
        $this->assertEquals('confirmed', $confirmedAppointments->first()->status);
    }

    public function test_for_date_scope_returns_appointments_for_specific_date(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        Appointment::create([
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        Appointment::create([
            'appointment_date' => '2024-01-16',
            'appointment_time' => '15:00',
            'service_id' => $service->id,
            'client_email' => 'jane@example.com',
            'client_name' => 'Jane Doe',
        ]);

        $appointmentsForDate = Appointment::forDate('2024-01-15')->get();
        $this->assertCount(1, $appointmentsForDate);
        $this->assertEquals('2024-01-15', $appointmentsForDate->first()->appointment_date->toDateString());
    }

    public function test_cannot_create_duplicate_appointment_for_same_time_slot(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        Appointment::create([
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Appointment::create([
            'appointment_date' => '2024-01-15',
            'appointment_time' => '14:00', // Same date and time
            'service_id' => $service->id,
            'client_email' => 'jane@example.com',
            'client_name' => 'Jane Doe',
        ]);
    }
}
