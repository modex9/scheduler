<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_service(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);
    }

    public function test_duration_formatted_attribute_returns_correct_format(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 90,
        ]);

        $this->assertEquals('1h 30m', $service->duration_formatted);
    }

    public function test_duration_formatted_for_hours_only(): void
    {
        $service = Service::create([
            'name' => 'Long Service',
            'duration_minutes' => 120,
        ]);

        $this->assertEquals('2h', $service->duration_formatted);
    }

    public function test_duration_formatted_for_minutes_only(): void
    {
        $service = Service::create([
            'name' => 'Quick Service',
            'duration_minutes' => 30,
        ]);

        $this->assertEquals('30m', $service->duration_formatted);
    }

    public function test_active_scope_returns_only_active_services(): void
    {
        Service::create([
            'name' => 'Active Service',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        Service::create([
            'name' => 'Inactive Service',
            'duration_minutes' => 60,
            'is_active' => false,
        ]);

        $activeServices = Service::active()->get();

        $this->assertCount(1, $activeServices);
        $this->assertTrue($activeServices->first()->is_active);
    }

    public function test_can_attach_working_hours_to_service(): void
    {
        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
        ]);

        $workingHour = WorkingHour::create([
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $service->workingHours()->attach($workingHour->id);

        $this->assertCount(1, $service->workingHours);
        $this->assertEquals(1, $service->workingHours->first()->day_of_week);
    }
}
