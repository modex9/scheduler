<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-api-endpoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the API endpoints to demonstrate functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing API Endpoints ===');

        // Test 1: Get Services
        $this->info("\n📋 Testing GET /api/services");
        $response = Http::get('http://localhost:8000/api/services');
        if ($response->successful()) {
            $this->line("  ✅ Services retrieved successfully");
            $services = $response->json('data.services');
            $this->line("  📊 Found {$response->json('data.total')} services");
        } else {
            $this->error("  ❌ Failed to get services");
        }

        // Test 2: Get Working Hours
        $this->info("\n🕐 Testing GET /api/working-hours");
        $response = Http::get('http://localhost:8000/api/working-hours');
        if ($response->successful()) {
            $this->line("  ✅ Working hours retrieved successfully");
            $this->line("  📊 Found {$response->json('data.total')} working hour rules");
        } else {
            $this->error("  ❌ Failed to get working hours");
        }

        // Test 3: Get Availability for next Monday
        $monday = now()->next('Monday')->format('Y-m-d');
        $this->info("\n📅 Testing GET /api/availability/slots?date={$monday}");
        $response = Http::get("http://localhost:8000/api/availability/slots?date={$monday}");
        if ($response->successful()) {
            $this->line("  ✅ Availability retrieved successfully");
            $this->line("  📊 Found {$response->json('data.total_slots')} available slots");
        } else {
            $this->error("  ❌ Failed to get availability");
        }

        // Test 4: Book an Appointment (if services exist)
        $service = Service::first();
        if ($service) {
            $this->info("\n📝 Testing POST /api/appointments");
            $appointmentData = [
                'appointment_date' => $monday,
                'appointment_time' => '09:00',
                'service_id' => $service->id,
                'client_email' => 'test@example.com',
                'client_name' => 'Test User',
                'notes' => 'API test appointment',
            ];

            $response = Http::post('http://localhost:8000/api/appointments', $appointmentData);
            if ($response->successful()) {
                $this->line("  ✅ Appointment booked successfully");
                $appointmentId = $response->json('data.appointment.id');
                $this->line("  📋 Appointment ID: {$appointmentId}");
            } else {
                $this->error("  ❌ Failed to book appointment");
                $this->line("  📄 Response: " . $response->body());
            }
        } else {
            $this->warn("  ⚠️  No services found, skipping appointment booking test");
        }

        $this->info("\n✅ API endpoint testing complete!");
        $this->info("\n💡 To test manually, start the server with: php artisan serve");
        $this->info("🌐 Then visit: http://localhost:8000/api/services");

        return Command::SUCCESS;
    }
}
