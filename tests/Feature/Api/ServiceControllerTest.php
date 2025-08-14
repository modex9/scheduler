<?php

namespace Tests\Feature\Api;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_get_all_services(): void
    {
        $response = $this->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'services',
                    'total'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 3
                ]
            ]);

        $services = $response->json('data.services');
        $this->assertCount(3, $services);
    }

    public function test_can_get_specific_service(): void
    {
        $service = Service::first();

        $response = $this->getJson("/api/services/{$service->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'service' => [
                        'id',
                        'name',
                        'duration_minutes',
                        'is_active'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name,
                    ]
                ]
            ]);
    }

    public function test_can_create_service(): void
    {
        $serviceData = [
            'name' => 'New Service',
            'duration_minutes' => 90,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/services', $serviceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'service' => [
                        'id',
                        'name',
                        'duration_minutes',
                        'is_active'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Service created successfully',
                'data' => [
                    'service' => [
                        'name' => 'New Service',
                        'duration_minutes' => 90,
                        'is_active' => true,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('services', [
            'name' => 'New Service',
            'duration_minutes' => 90,
        ]);
    }

    public function test_cannot_create_service_with_invalid_duration(): void
    {
        $serviceData = [
            'name' => 'Invalid Service',
            'duration_minutes' => 10, // Too short
        ];

        $response = $this->postJson('/api/services', $serviceData);

        $response->assertStatus(422);
    }

    public function test_cannot_create_service_with_invalid_duration_too_long(): void
    {
        $serviceData = [
            'name' => 'Invalid Service',
            'duration_minutes' => 600, // Too long (10 hours)
        ];

        $response = $this->postJson('/api/services', $serviceData);

        $response->assertStatus(422);
    }

    public function test_can_update_service(): void
    {
        $service = Service::first();
        $updateData = [
            'name' => 'Updated Service',
            'duration_minutes' => 120,
        ];

        $response = $this->putJson("/api/services/{$service->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service updated successfully',
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => 'Updated Service',
                        'duration_minutes' => 120,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service',
            'duration_minutes' => 120,
        ]);
    }

    public function test_can_delete_service(): void
    {
        $service = Service::first();

        $response = $this->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service deleted successfully',
            ]);

        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
        ]);
    }

    public function test_returns_404_for_nonexistent_service(): void
    {
        $response = $this->getJson('/api/services/999');

        $response->assertStatus(404);
    }
}
