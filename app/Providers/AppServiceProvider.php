<?php

namespace App\Providers;

use App\Repositories\CafeManager\CafeRepository;
use App\Services\CafeManager\CafeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CafeService::class);
        $this->app->bind(CafeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        
    }
}
