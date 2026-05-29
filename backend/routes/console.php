<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:expire')->daily();
Schedule::command('subscriptions:send-expiry-reminders')->dailyAt('09:00');
Schedule::command('races:send-reminders')->dailyAt('08:00');
Schedule::command('inscriptions:send-pending-digest')->dailyAt('09:30');
