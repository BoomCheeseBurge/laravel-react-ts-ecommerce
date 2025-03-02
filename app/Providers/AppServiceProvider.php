<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Schedule monthly vendor payout
        Schedule::command('payout:vendors')
                ->monthlyOn(1, '0:0')
                ->withoutOverlapping(); // Prevent distributed servers to run this command multiple times concurrently by two or more servers

        Vite::prefetch(concurrency: 3);
    }
}
