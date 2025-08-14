<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkingHourTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_working_hour(): void
    {
        $workingHour = WorkingHour::create([
            'day_of_week' => 1, // Monday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('working_hours', [
            'id' => $workingHour->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => 1,
        ]);
    }

    public function test_day_name_attribute_returns_correct_day(): void
    {
        $workingHour = WorkingHour::create([
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->assertEquals('Monday', $workingHour->day_name);
    }

    public function test_active_scope_returns_only_active_working_hours(): void
    {
        WorkingHour::create([
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);

        WorkingHour::create([
            'day_of_week' => 2,
            'start_time' => '10:00',
            'end_time' => '18:00',
            'is_active' => false,
        ]);

        $activeHours = WorkingHour::active()->get();

        $this->assertCount(1, $activeHours);
        $this->assertTrue($activeHours->first()->is_active);
    }

    public function test_can_attach_services_to_working_hours(): void
    {
        $workingHour = WorkingHour::create([
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $workingHour->services()->attach($service->id);

        $this->assertCount(1, $workingHour->services);
        $this->assertEquals('Haircut', $workingHour->services->first()->name);
    }

    public function test_cannot_create_duplicate_day_of_week(): void
    {
        WorkingHour::create([
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        WorkingHour::create([
            'day_of_week' => 1, // Same day
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);
    }
}
