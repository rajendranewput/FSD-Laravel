<?php

namespace App\Providers;

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
        //
        define('CAFE_FLAG', 0);
        define('CAMPUS_FLAG', 1);
        define('CAMPUS_SUMMARY_FLAG', 2);
       define('CAFE_SUMMARY_FLAG', 3);
       define('ITEMS_PER_PAGE', 1);
       define('ACCOUNT_SUMMARY_FLAG', 5);
       define('DM_FLAG', 6);
       define('DM_SUMMARY_FLAG', 7);
       define('RVP_FLAG', 8);
       define('RVP_SUMMARY_FLAG', 9);
       define('COMPANY_FLAG', 10);
       define('COMPANY_SUMMARY_FLAG', 11);
       define('ACCOUNT_FLAG', 4);
    }
}
