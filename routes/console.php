<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Jadwal Tugas Otomatis
|--------------------------------------------------------------------------
| Dijalankan oleh `schedule:work` (docker-compose queue-worker).
*/
Schedule::command('news:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();
