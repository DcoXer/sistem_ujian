<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

return function (\Illuminate\Console\Scheduling\Schedule $schedule) {
    // every minute we mark any exam whose end time has passed as finished.
    $schedule->command('exams:finish-expired')->everyMinute();
    // every minute we lock active attempts whose time has ended.
    $schedule->command('exams:expire-attempts')->everyMinute();
    // prewarm static question content ahead of start spikes.
    $schedule->command('exams:warm-content --window=15')->everyFiveMinutes();
    // prune aged audit logs daily based on retention policy.
    $schedule->command('security:prune-audits')->daily();
};
