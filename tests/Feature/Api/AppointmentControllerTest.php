<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_book_appointment(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');
        $service = Service::first();

        $response = $this->postJson('/api/appointments', [
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
            'notes' => 'First time client',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'appointment' => [
                        'id',
                        'appointment_date',
                        'appointment_time',
                        'service_id',
                        'client_email',
                        'client_name',
                        'status',
                        'notes',
                        'service'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => [
                    'appointment' => [
                        'appointment_date' => $monday,
                        'appointment_time' => '09:00',
                        'client_email' => 'john@example.com',
                        'client_name' => 'John Doe',
                        'status' => 'confirmed',
                        'notes' => 'First time client',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('appointments', [
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);
    }

    public function test_cannot_book_appointment_for_past_date(): void
    {
        $pastDate = now()->subDay()->format('Y-m-d');
        $service = Service::first();

        $response = $this->postJson('/api/appointments', [
            'appointment_date' => $pastDate,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_cannot_book_appointment_with_invalid_service(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');

        $response = $this->postJson('/api/appointments', [
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => 999,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_appointment_with_invalid_email(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');
        $service = Service::first();

        $response = $this->postJson('/api/appointments', [
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'invalid-email',
            'client_name' => 'John Doe',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_duplicate_appointment(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');
        $service = Service::first();

        // Book first appointment
        $this->postJson('/api/appointments', [
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        // Try to book duplicate
        $response = $this->postJson('/api/appointments', [
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'jane@example.com',
            'client_name' => 'Jane Doe',
        ]);

        $response->assertStatus(500); // Should fail due to unique constraint
    }

    public function test_can_get_appointment_details(): void
    {
        $service = Service::first();
        $appointment = Appointment::create([
            'appointment_date' => now()->next('Monday')->format('Y-m-d'),
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        $response = $this->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'appointment' => [
                        'id',
                        'appointment_date',
                        'appointment_time',
                        'service_id',
                        'client_email',
                        'client_name',
                        'status',
                        'service'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'appointment' => [
                        'id' => $appointment->id,
                        'client_email' => 'john@example.com',
                        'client_name' => 'John Doe',
                    ]
                ]
            ]);
    }

    public function test_can_cancel_appointment(): void
    {
        $service = Service::first();
        $appointment = Appointment::create([
            'appointment_date' => now()->next('Monday')->format('Y-m-d'),
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        $response = $this->deleteJson("/api/appointments/{$appointment->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_get_appointments_for_date_range(): void
    {
        $service = Service::first();
        $monday = now()->next('Monday')->format('Y-m-d');
        $tuesday = now()->next('Tuesday')->format('Y-m-d');

        // Create appointments
        Appointment::create([
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'john@example.com',
            'client_name' => 'John Doe',
        ]);

        Appointment::create([
            'appointment_date' => $tuesday,
            'appointment_time' => '10:00',
            'service_id' => $service->id,
            'client_email' => 'jane@example.com',
            'client_name' => 'Jane Doe',
        ]);

        $response = $this->getJson("/api/appointments/range?start_date={$monday}&end_date={$tuesday}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'appointments',
                    'total'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 2
                ]
            ]);
    }
}
