<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Console\Command;

class VerifyDatabaseSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verify-database-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the database setup and display current data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Database Setup Verification ===');

        // Check Working Hours
        $this->info("\n📅 Working Hours:");
        $workingHours = WorkingHour::all();
        if ($workingHours->count() > 0) {
            foreach ($workingHours as $workingHour) {
                $this->line("  • {$workingHour->day_name}: {$workingHour->start_time} - {$workingHour->end_time}");
            }
        } else {
            $this->warn("  No working hours found. Run 'php artisan db:seed' to populate data.");
        }

        // Check Services
        $this->info("\n💇 Services:");
        $services = Service::all();
        if ($services->count() > 0) {
            foreach ($services as $service) {
                $this->line("  • {$service->name} ({$service->duration_formatted}) - \${$service->price}");
            }
        } else {
            $this->warn("  No services found. Run 'php artisan db:seed' to populate data.");
        }

        // Check Relationships
        if ($services->count() > 0 && $workingHours->count() > 0) {
            $this->info("\n🔗 Service-Working Hour Relationships:");
            $service = $services->first();
            $this->line("  • {$service->name} is available on: " . $service->workingHours->pluck('day_name')->implode(', '));
        }

        $this->info("\n✅ Database setup verification complete!");

        return Command::SUCCESS;
    }
}
