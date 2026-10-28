<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cycle automatique des tournois (Lundi, Mercredi, Vendredi)
Schedule::command('tournaments:auto-schedule')->everyFiveMinutes();

// Résolution des combats et injection des bots au besoin
Schedule::command('tournament:process')->everyMinute();
Schedule::command('tournaments:auto-fill-bots')->everyMinute();
