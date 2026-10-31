<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Release plots whose hold period has ended (needs the server cron: * * * * * php artisan schedule:run).
Schedule::command('reservations:expire')->dailyAt('00:15')->withoutOverlapping();
