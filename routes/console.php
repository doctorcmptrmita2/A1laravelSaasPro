<?php

use App\Jobs\SyncLiteLLMCosts;
use App\Jobs\SyncLiteLLMLogs;
use App\Jobs\SyncLiteLLMUsage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Jobs for LiteLLM Sync
Schedule::job(new SyncLiteLLMLogs)->everyFiveMinutes();
Schedule::job(new SyncLiteLLMUsage)->everyFifteenMinutes();
Schedule::job(new SyncLiteLLMCosts)->hourly();
