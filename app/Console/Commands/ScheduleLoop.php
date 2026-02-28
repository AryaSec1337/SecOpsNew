<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ScheduleLoop extends Command
{
    protected $signature = 'schedule:loop';
    protected $description = 'Run the scheduler in a loop (Windows Friendly)';

    public function handle()
    {
        $this->info("Starting Scheduler Loop... (Press Ctrl+C to stop)");

        while (true) {
            $this->line("[" . now()->format('H:i:s') . "] Running schedule:run...");
            
            // Execute schedule:run directly using passthru to share output
            passthru('php artisan schedule:run');

            $this->line("Sleeping for 60 seconds...");
            sleep(60);
        }
    }
}
