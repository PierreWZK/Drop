<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('drops:expire-reservations')->everyMinute()->withoutOverlapping();
// Idempotent : crée le drop d'aujourd'hui (avant 18 h) et celui de demain s'ils n'existent pas. Toutes les heures
// pour rattraper un redémarrage du conteneur.
Schedule::command('drops:draw')->hourly()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
