<?php

namespace Tests\Feature\Api;

use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkingHourControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_get_all_working_hours(): void
    {
        $response = $this->getJson('/api/working-hours');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'working_hours',
                    'total'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 5
                ]
            ]);

        $workingHours = $response->json('data.working_hours');
        $this->assertCount(5, $workingHours);
    }

    public function test_can_get_specific_working_hour(): void
    {
        $workingHour = WorkingHour::first();

        $response = $this->getJson("/api/working-hours/{$workingHour->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'working_hour' => [
                        'id',
                        'day_of_week',
                        'start_time',
                        'end_time',
                        'is_active'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'working_hour' => [
                        'id' => $workingHour->id,
                        'day_of_week' => $workingHour->day_of_week,
                    ]
                ]
            ]);
    }

    public function test_can_create_working_hour(): void
    {
        $workingHourData = [
            'day_of_week' => 6, // Saturday
            'start_time' => '10:00',
            'end_time' => '16:00',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/working-hours', $workingHourData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'working_hour' => [
                        'id',
                        'day_of_week',
                        'start_time',
                        'end_time',
                        'is_active'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Working hour created successfully',
                'data' => [
                    'working_hour' => [
                        'day_of_week' => 6,
                        'start_time' => '10:00',
                        'end_time' => '16:00',
                        'is_active' => true,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('working_hours', [
            'day_of_week' => 6,
            'start_time' => '10:00',
            'end_time' => '16:00',
            'is_active' => 1,
        ]);
    }

    public function test_cannot_create_working_hour_with_invalid_day(): void
    {
        $workingHourData = [
            'day_of_week' => 7, // Invalid day
            'start_time' => '10:00',
            'end_time' => '16:00',
        ];

        $response = $this->postJson('/api/working-hours', $workingHourData);

        $response->assertStatus(422);
    }

    public function test_cannot_create_working_hour_with_end_time_before_start_time(): void
    {
        $workingHourData = [
            'day_of_week' => 1,
            'start_time' => '16:00',
            'end_time' => '10:00', // End before start
        ];

        $response = $this->postJson('/api/working-hours', $workingHourData);

        $response->assertStatus(422);
    }

    public function test_cannot_create_duplicate_day_of_week(): void
    {
        // Try to create another working hour for Monday (day 1)
        $workingHourData = [
            'day_of_week' => 1, // Monday already exists
            'start_time' => '18:00',
            'end_time' => '20:00',
        ];

        $response = $this->postJson('/api/working-hours', $workingHourData);

        $response->assertStatus(500); // Should fail due to unique constraint
    }

    public function test_can_update_working_hour(): void
    {
        $workingHour = WorkingHour::first();
        $updateData = [
            'start_time' => '08:00',
            'end_time' => '18:00',
        ];

        $response = $this->putJson("/api/working-hours/{$workingHour->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Working hour updated successfully',
                'data' => [
                    'working_hour' => [
                        'id' => $workingHour->id,
                        'start_time' => '08:00',
                        'end_time' => '18:00',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('working_hours', [
            'id' => $workingHour->id,
            'start_time' => '08:00',
            'end_time' => '18:00',
        ]);
    }

    public function test_can_delete_working_hour(): void
    {
        $workingHour = WorkingHour::first();

        $response = $this->deleteJson("/api/working-hours/{$workingHour->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Working hour deleted successfully',
            ]);

        $this->assertDatabaseMissing('working_hours', [
            'id' => $workingHour->id,
        ]);
    }

    public function test_returns_404_for_nonexistent_working_hour(): void
    {
        $response = $this->getJson('/api/working-hours/999');

        $response->assertStatus(404);
    }
}
