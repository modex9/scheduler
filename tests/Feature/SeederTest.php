<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_database_with_sample_data(): void
    {
        $this->seed();

        // Check working hours were created
        $this->assertCount(5, WorkingHour::all());

        // Check services were created
        $this->assertCount(3, Service::all());

        // Check relationships were created
        $service = Service::first();
        $this->assertCount(5, $service->workingHours);

        $workingHour = WorkingHour::first();
        $this->assertCount(3, $workingHour->services);
    }

    public function test_working_hours_are_for_weekdays_only(): void
    {
        $this->seed();

        $workingHours = WorkingHour::all();

        foreach ($workingHours as $workingHour) {
            $this->assertGreaterThanOrEqual(1, $workingHour->day_of_week);
            $this->assertLessThanOrEqual(5, $workingHour->day_of_week);
        }
    }

    public function test_services_have_reasonable_durations(): void
    {
        $this->seed();

        $services = Service::all();

        foreach ($services as $service) {
            $this->assertGreaterThan(0, $service->duration_minutes);
            $this->assertLessThanOrEqual(240, $service->duration_minutes); // Max 4 hours
        }
    }
}
