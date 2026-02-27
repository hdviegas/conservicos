<?php

namespace App\Providers;

use App\Models\TimeRecord;
use App\Observers\TimeRecordObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        TimeRecord::observe(TimeRecordObserver::class);
    }
}
