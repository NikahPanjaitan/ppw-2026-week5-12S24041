<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Menstandarkan styling pagination bawaan Laravel agar cocok dengan Bootstrap 5
        Paginator::useBootstrapFive();

        // Audit Log SQL: Mencatat seluruh kueri SQL ke storage/logs/laravel.log untuk membuktikan pencegahan N+1 Query Problem
        DB::listen(function ($query) {
            Log::info(sprintf(
                "[SQL AUDIT] (%.2f ms) %s",
                $query->time,
                $query->sql
            ));
        });
    }
}
