<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NPlusOneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_availability_service_does_not_have_n_plus_one_queries(): void
    {
        // Create some appointments to test with
        $service = Service::first();
        $monday = now()->next('Monday')->format('Y-m-d');
        
        // Create multiple appointments
        for ($i = 0; $i < 5; $i++) {
            Appointment::create([
                'appointment_date' => $monday,
                'appointment_time' => sprintf('09:%02d', $i * 10),
                'service_id' => $service->id,
                'client_email' => "test{$i}@example.com",
                'status' => 'confirmed',
            ]);
        }

        // Clear query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Call the availability service
        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots($monday, $service->id);

        // Get the query log
        $queries = DB::getQueryLog();
        
        // Count queries that access the service relationship
        $serviceQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'services');
        })->count();

        // We should have only 1 query to load services (not N+1)
        $this->assertLessThanOrEqual(2, $serviceQueries, 
            'N+1 query problem detected. Expected 1-2 service queries, got ' . $serviceQueries);
        
        // Verify we got some slots back
        $this->assertGreaterThan(0, $slots->count());
    }
}
