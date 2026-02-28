<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// CTI Automation
Illuminate\Support\Facades\Schedule::command('cti:scan-domains')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/cti_scan.log'));
Illuminate\Support\Facades\Schedule::command('cti:scan-typosquat')->weeklyOn(1, '02:00'); // Weekly on Monday at 2 AM
Illuminate\Support\Facades\Schedule::command('siem:cleanup')->dailyAt('01:00'); // Daily Cleanup at 1 AM

