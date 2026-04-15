<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 毎日 01:00 WIB（UTC+7）に自動実行
// ※ XAMPPローカルはtimezone=Asia/Jakartaなので offset 不要
Schedule::command('hri:expire-estimates')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta')
    ->appendOutputTo(storage_path('logs/cron-estimates.log'));

Schedule::command('hri:mark-invoices-overdue')
    ->dailyAt('01:05')
    ->timezone('Asia/Jakarta')
    ->appendOutputTo(storage_path('logs/cron-invoices.log'));