<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Process\Process;

class ServeCommand extends BaseServeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serve {--raw : Run the raw php artisan serve without concurrently} {--port=8000 : The port to serve the application on} {--host=127.0.0.1 : The host address to serve the application on} {--tries=10 : The number of tries} {--no-reload : Do not reload the development server on .env file changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application on the PHP development server (Unified with Vite & Redis)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // If the 'raw' flag is present, behave exactly like the parent command.
        // We use this flag in the 'concurrently' command to avoid infinite loops.
        if ($this->option('raw')) {
            return parent::handle();
        }

        $this->components->info('Starting Unified SecOps Server: Laravel + Vite + Redis...');
        $this->line(' <fg=gray>│</> Press <fg=red>Ctrl+C</> to stop the server.');
        $this->newLine();

        // Delegate to the npm script which runs concurrently
        // "php artisan serve --raw" + "npm run dev" + "redis-server"
        // We use system() to pass through input/output directly
        // Check if host is not localhost, if so use lan-serve script
        $host = $this->option('host');
        if ($host && $host !== '127.0.0.1' && $host !== 'localhost') {
             $this->components->info("Starting in Network Mode (Host: $host)...");
             passthru('npm run lan-serve', $status);
        } else {
             passthru('npm run dev-server', $status);
        }

        return $status;
    }
}
