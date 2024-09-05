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
        define('PRODUCE_GL_CODE', 411028);
        define('MEAT_GL_CODE', 411029);
        define('CHEESE_GL_CODE', 411031);
        define('FLUID_DAIRY_GL_CODE', 411032);
        define('SEAFOOD_GL_CODE', 411136);
        define('SUSHI_GL_CODE', 411137);
        define('BAKERY_GL_CODE', 411138);
        define('ARTISAN_GL_CODE', 411139);
        define('COFFEE_GL_CODE', 411140);
        define('LOCALLY_CRAFTED_GL_CODE', 411141);
        define('F2F_EXP_ARRAY_ONE', array('411028', '411029', '411031', '411032', '411136', '411137', '411138', '411139', '411140', '411141'));
        define('F2F_EXP_ARRAY_TWO', array('411028', '411029', '411031', '411032', '411136', '411137', '411138', '411139', '411140', '411141', '411036', '411037', '411038', '411039', '411041', '411045', '411048', '411060', '411061', '411071', '411072', '411073', '411074', '411076', '411085', '411086', '411100'));
        define('COR_SECTION', 'cor');
        define('COOKED_LEAKAGE_SECTION', 'cookedLeakage');
        define('FARM_FORK_SECTION', 'farmToFork');
        define('PPS_COLOR_DIVIDE_VALUE', 0);
        define('FF_COLOR_DIVIDE_VALUE', 15);
        define('FF_FULL_CIRCLE_VALUE', 20);
    }
}
