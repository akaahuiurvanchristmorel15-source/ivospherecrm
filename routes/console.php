<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clôture automatique des départs non scannés chaque jour à 20:00
Schedule::command('rh:auto-checkout')->dailyAt('20:00');

// Génération automatique des évaluations mensuelles le 5 de chaque mois à 02:00
Schedule::command('rh:generate-evaluations')->monthlyOn(5, '02:00');
