<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import emails from public/emails.csv to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = public_path('emails.csv');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $this->info("Importing emails from $file...");

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';'); // Skip header with semicolon delimiter

        // "Display name","Email address","Recipient type"
        // Index 0, 1, 2

        $count = 0;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (empty($row[1])) continue; // Skip if no email

            \App\Models\EmailsUser::updateOrCreate(
                ['email_address' => $row[1]],
                [
                    'display_name' => $row[0] ?? null,
                    'recipient_type' => $row[2] ?? null,
                ]
            );
            $count++;
            if ($count % 100 == 0) $this->info("Imported $count records...");
        }

        fclose($handle);

        $this->info("Done! Total imported: $count");
    }
}
