<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create working hours for Monday to Friday
        $workingHours = [
            ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00'], // Monday
            ['day_of_week' => 2, 'start_time' => '09:00', 'end_time' => '17:00'], // Tuesday
            ['day_of_week' => 3, 'start_time' => '09:00', 'end_time' => '17:00'], // Wednesday
            ['day_of_week' => 4, 'start_time' => '09:00', 'end_time' => '17:00'], // Thursday
            ['day_of_week' => 5, 'start_time' => '09:00', 'end_time' => '17:00'], // Friday
        ];

        foreach ($workingHours as $workingHour) {
            WorkingHour::create($workingHour);
        }

        // Create sample services
        $services = [
            [
                'name' => 'Haircut',
                'duration_minutes' => 60,
                'price' => 50.00,
                'description' => 'Professional haircut and styling',
            ],
            [
                'name' => 'Hair Coloring',
                'duration_minutes' => 120,
                'price' => 100.00,
                'description' => 'Full hair coloring service',
            ],
            [
                'name' => 'Quick Trim',
                'duration_minutes' => 30,
                'price' => 25.00,
                'description' => 'Quick trim and cleanup',
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        // Attach all services to all working hours
        $services = Service::all();
        $workingHours = WorkingHour::all();

        foreach ($services as $service) {
            $service->workingHours()->attach($workingHours->pluck('id'));
        }
    }
}
