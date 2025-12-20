<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pending TTL: 10 dk
Schedule::command('app:expire-pending-payment-attempts --minutes=10')
    ->everyMinute();

// Completed işaretleme: saatlik
Schedule::command('app:mark-completed-orders')
    ->hourly();
