<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mantra:import {path?}', function ($path = null) {
    $this->call(\App\Console\Commands\ImportMantraCommand::class, array_filter(['path' => $path]));
})->purpose('Import data MANTRA Excel ke database');

Artisan::command('import:mantra {path?}', function ($path = null) {
    $this->call(\App\Console\Commands\ImportMantraCommand::class, array_filter(['path' => $path]));
})->purpose('Import data MANTRA Excel ke database');
