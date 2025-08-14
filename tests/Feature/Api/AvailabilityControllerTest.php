<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_get_available_slots_returns_available_time_slots(): void
    {
        // Get a future Monday
        $monday = now()->next('Monday')->format('Y-m-d');

        $response = $this->getJson("/api/availability/slots?date={$monday}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date',
                    'available_slots',
                    'total_slots'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => $monday
                ]
            ]);

        // Should have available slots for Monday
        $this->assertGreaterThan(0, $response->json('data.total_slots'));
    }

    public function test_get_available_slots_with_service_filter(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');
        $service = Service::first();

        $response = $this->getJson("/api/availability/slots?date={$monday}&service_id={$service->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => $monday
                ]
            ]);

        // All slots should be for the specified service
        $slots = $response->json('data.available_slots');
        foreach ($slots as $slot) {
            $this->assertEquals($service->id, $slot['service_id']);
        }
    }

    public function test_get_available_slots_for_weekend_returns_empty(): void
    {
        // Get a future Sunday (weekend)
        $sunday = now()->next('Sunday')->format('Y-m-d');

        $response = $this->getJson("/api/availability/slots?date={$sunday}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => $sunday,
                    'total_slots' => 0
                ]
            ]);
    }

    public function test_get_available_slots_for_past_date_returns_error(): void
    {
        $pastDate = now()->subDay()->format('Y-m-d');

        $response = $this->getJson("/api/availability/slots?date={$pastDate}");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_get_available_slots_with_invalid_service_id(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');

        $response = $this->getJson("/api/availability/slots?date={$monday}&service_id=999");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_check_slot_availability_returns_true_for_available_slot(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');
        $service = Service::first();

        $response = $this->getJson("/api/availability/check?date={$monday}&time=09:00&service_id={$service->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date',
                    'time',
                    'service_id',
                    'is_available'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => $monday,
                    'time' => '09:00',
                    'service_id' => $service->id,
                    'is_available' => true
                ]
            ]);
    }

    public function test_check_slot_availability_returns_false_for_booked_slot(): void
    {
        $monday = now()->next('Monday')->format('Y-m-d');
        $service = Service::first();

        // Create an appointment for this slot
        Appointment::create([
            'appointment_date' => $monday,
            'appointment_time' => '09:00',
            'service_id' => $service->id,
            'client_email' => 'test@example.com',
            'client_name' => 'Test User',
        ]);

        $response = $this->getJson("/api/availability/check?date={$monday}&time=09:00&service_id={$service->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_available' => false
                ]
            ]);
    }

    public function test_check_slot_availability_with_invalid_data_returns_error(): void
    {
        $response = $this->getJson("/api/availability/check?date=invalid&time=25:00&service_id=999");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }
}
