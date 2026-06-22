<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:send-module-renewal-reminders')
    ->dailyAt((string) config('billing.module_billing.schedule_time', '08:00'))
    ->timezone((string) config('billing.module_billing.schedule_timezone', 'America/Tegucigalpa'));

Schedule::command('billing:process-renewals')
    ->dailyAt((string) config('billing.engine.process_time', '08:00'))
    ->timezone((string) config('billing.engine.timezone', 'America/Tegucigalpa'));
