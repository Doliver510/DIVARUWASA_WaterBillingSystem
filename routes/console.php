<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Payment reminders are sent daily at 8:00 AM:
| - 3 days before due date: Friendly reminder
| - Day after due date: Penalty applied notification
|
| To run the scheduler, set up a cron job:
| * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
| For Windows Task Scheduler, run every minute:
| php artisan schedule:run
|
*/

Schedule::command('bills:send-reminders --days-before=3')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->description('Send payment reminder emails to consumers');
