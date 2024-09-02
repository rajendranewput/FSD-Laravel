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
        define('BEEF_CODE', 'MCC-10069');
        define('CHICKEN_CODE', 'MCC-10048');
        define('TURKEY_CODE', 'MCC-10053');
        define('PORK_CODE', 'MCC-10066');
        define('EGGS_CODE', 'MCC-10025');
        define('DAIRY_PRODUCT_CODE', 'MCC-10067');
        define('FISH_AND_SEEFOOD_CODE', 'MCC-10021');
        define('COR_COLOR_DIVIDE_VALUE', 90);
        define('INDICATOR_POSITIVE', '#63BF87');
        define('INDICATOR_NEGATIVE', '#E35E56');
        define('EXCLUDED_COSTCENTERS', array('AGH000', 'AEV000', 'AEW000'));

    }
}
