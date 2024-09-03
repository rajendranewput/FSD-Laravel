<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

class ArrayConditionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind a condition to the service container

        $this->app->bind('check.campusRollSummary', function ($app) {
            return function (Request $request) {
                $constants = config('constants');
                return in_array($request->campusRollUp, array($constants['CAMPUS_SUMMARY_FLAG'], $constants['CAFE_SUMMARY_FLAG'], $constants['ACCOUNT_SUMMARY_FLAG'], $constants['DM_SUMMARY_FLAG'], $constants['RVP_SUMMARY_FLAG'], $constants['COMPANY_SUMMARY_FLAG']));
            };
        });

        $this->app->bind('check.allLevelFlag', function ($app) {
            return function (Request $request) {
                $constants = config('constants');
                return in_array($request->campusRollUp, array($constants['CAMPUS_FLAG'], $constants['ACCOUNT_FLAG'], $constants['DM_FLAG'], $constants['RVP_FLAG'], $constants['COMPANY_FLAG']));
            };
        });

        $this->app->bind('check.accCampusRoll', function ($app) {
            return function (Request $request) {
                $constants = config('constants');
                $firstConditionMet = app('check.campusRollSummary')($request);
                if ($firstConditionMet) {
                    return in_array($request->campusRollUp, array($constants['ACCOUNT_FLAG'], $constants['ACCOUNT_SUMMARY_FLAG']));
                }
            };
        });

        $this->app->bind('check.campusRoll', function ($app) {
            return function (Request $request) {
                $constants = config('constants');
                $firstConditionMet = app('check.campusRollSummary')($request);
                if ($firstConditionMet) {
                    return in_array($request->campusRollUp, array($constants['DM_FLAG'], $constants['DM_SUMMARY_FLAG'], $constants['RVP_FLAG'], $constants['RVP_SUMMARY_FLAG'], $constants['COMPANY_FLAG'], $constants['COMPANY_SUMMARY_FLAG']));
                }
            };
        });

        $this->app->bind('check.date', function ($app) {
            return function (Request $request) {
                if (strpos($request->date, ',') !== false) {
                    $date = explode(',', $request->date);
                } else {
                    $date = $request->date;
                }

                if($date) {
                    return $date;
                }
            };
        });

        $this->app->bind('join.costCenters', function ($app) {
            return function ($costCenters, $campusRollUp) {
                $constants = config('constants');
                if(in_array($campusRollUp, array($constants['CAMPUS_FLAG'], $constants['CAMPUS_SUMMARY_FLAG'], $constants['ACCOUNT_FLAG'], $constants['ACCOUNT_SUMMARY_FLAG'], $constants['DM_FLAG'], $constants['DM_SUMMARY_FLAG'], $constants['RVP_FLAG'], $constants['RVP_SUMMARY_FLAG'], $constants['COMPANY_FLAG'], $constants['COMPANY_SUMMARY_FLAG']))){
                    $costCenters = explode(',',$costCenters);
                    return $costCenters;
                }
                else {
                    return $request->$costCenters;
                }
            };
        });

        $this->app->bind('fiscal.year', function ($app) {
            return function ($date) {
                if(is_array($date)){
                    foreach($date as $dates){
                        $d = new DateTime($dates);
                        $endDates[] = $d->format('n/d/Y');
                        
                    }
                    $yearQuery = DB::table('dashboard_fiscal_periods')->select('fiscal_year')
                        ->whereIn('end_date', $endDates)
                        ->first();
                    $year = $yearQuery->fiscal_year;
                    return $year;
                }else{
                    $year = explode('-', $date);
                    return $year[0];
                }
            };
        });
        
        $this->app->bind('color.threshold', function ($app) {
            return function ($value) {
                $constants = config('constants');
                if($value >= $constants['FF_COLOR_DIVIDE_VALUE']){
                    $color = $constants['INDICATOR_POSITIVE'];
                } else {
                    $color = $constants['INDICATOR_NEGATIVE']; 
                }
                return $color;
            };
        });
        
        $this->app->bind('ff.response', function ($app) {
            return function ($displayValue, $colorThreshold, $lineItem, $trendGraph) {
                $constants = config('constants');
                $percentage = ($displayValue/$constants['FF_FULL_CIRCLE_VALUE'])*100;
                return array(
                    'percentage' => $percentage,
                    'display_value' => $displayValue,
                    'color_threshold' => $colorThreshold,
                    'line_item' => $lineItem,
                    'trend_graph' => $trendGraph,
                );
            };
        });

        $this->app->bind('ff.chart', function ($app) {
            return function ($amount) {
                if(isset($amount)){
                    return ($amount >= 0) ? round($amount) : 0;
                } else {
                    return $amount;
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
