<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Traits\PurchasingTrait;


class Purchasing extends Model
{
    use HasFactory, Notifiable, PurchasingTrait;

    // To fetch farm to fork data
    public static function farmToForkData($request, $costCenters, $date, $campusRollUp, $type, $fytdPeriods){
        $year = app('fiscal.year')($date);
        $checkAllLevel = app('check.allLevelFlag')($request);
        $checkCampusRollSummary = app('check.campusRollSummary')($request);
        $constants = config('constants');
        // Get item 1 calculation
        if(!is_array($costCenters)){
            $costCenters = explode(',', $costCenters);
        }
        $glQuery = DB::table('gl_codes')->whereIn('exp_1', $constants['F2F_EXP_ARRAY_ONE']);

        if($checkCampusRollSummary){
            $glQuery->whereIn('unit_id', $costCenters);
            $glQuery->where('processing_year', $year);
        } else{
            if($checkAllLevel){
                $glQuery->whereIn('unit_id', $costCenters);
            } else {
                $glQuery->whereIn('unit_id', $costCenters);
            }
            if($type == 'period'){
                $glQuery->whereIn('end_date', $date);
            } else {
                $glQuery->whereIn('end_date', $fytdPeriods);
            }
        }
        $amount = $glQuery->get()->sum('amount');
        $item1 = $amount;
        
        
        // Get item 2 calculation
        $glQuery = DB::table('gl_codes')->whereIn('exp_1', $constants['F2F_EXP_ARRAY_TWO']);
       
        if($checkCampusRollSummary){
            $glQuery->whereIn('unit_id', $costCenters);
            $glQuery->where('processing_year', $year);
        } else{
            if($checkAllLevel){
                $glQuery->whereIn('unit_id', $costCenters);
            } else {
                $glQuery->whereIn('unit_id', $costCenters);
            }
            if($type == 'period'){
                $glQuery->whereIn('end_date', $date);
            } else {
                $glQuery->whereIn('end_date', $fytdPeriods);
            }
        }
        $amount2 = $glQuery->get()->sum('amount');
        $item2 = $amount2;
        if(empty($item1) && empty($item2)){
            $result = null; 
        } else {
            if(empty($item1)){
                $result = 0;
            } 
            else{
                if(empty($item2)){
                    $result = null;
                } else {
                    $result = round(ABS($item1 / $item2)*100, 1);
                }
            }
        }
        return $result;
    }

    // To fetch fiscal year and period using end date
    public static function fytdPeriods($endDate) {
        if(is_array($endDate)){
            foreach($endDate as $dates){
                $date = new DateTime($dates);
                $endDates[] = $date->format('n/d/Y');
            }
        } else {
            $date = new DateTime($endDate);
            $endDates[] = $date->format('n/d/Y');
        }
        
        $yearQuery = DB::table('dashboard_fiscal_periods')->select('fiscal_year')
            ->whereIn('end_date', $endDates)
            ->first();  
        $year = $yearQuery->fiscal_year;
        
        $periodQuery = DB::table('dashboard_fiscal_periods')->select('fiscal_period')
            ->where('fiscal_year', $year)
            ->whereIn('end_date', $endDates)
            ->orderBy('fiscal_period', 'desc')
            ->first();  
        $fiscalPeriod = $periodQuery->fiscal_period;
        
        $dataQuery = DB::table('dashboard_fiscal_periods')->select('end_date')
            ->where('fiscal_year', $year)
            ->where('fiscal_period', '<=', DB::raw($fiscalPeriod))
            ->get();

        $endDateArray = [];
        foreach($dataQuery as $value){
            $endDateArray[] = date('Y-m-d', strtotime($value->end_date));
        }
        return $endDateArray;
    }

    // To fetch cooked from scratch data
    public static function cookedFromScratch($request, $costCenter, $date, $campusRollUp){
        if(!is_array($costCenter)){
            $costCenter = explode(',', $costCenter);
        }
        $checkAllLevel = app('check.allLevelFlag')($request);
        $checkCampusRollSummary = app('check.campusRollSummary')($request);
        $purchsesMetaQuery = DB::table('purchases_meta');
       
        if($checkCampusRollSummary){
            $year = app('fiscal.year')($date);
            $purchsesMetaQuery->where('processing_year', $year);
        } else{
            if(is_array($date)){
                foreach ($date as $dates ) {
                    $date = new DateTime($dates);
                    $endDates[] = $date->format('n/d/Y');
                }
            } else {
                $date = new DateTime($endDate);
                $endDates[] = $date->format('n/d/Y');
            }        
            $purchsesMetaQuery->whereIn('processing_month_date', $endDates);
        }

        if($checkAllLevel){
            $purchsesMetaQuery->whereIn('financial_code', $costCenter);
        } else{
            $purchsesMetaQuery->whereIn('financial_code', $costCenter);
        }
        $purchsesMetaQuery->select(DB::raw('SUM(IF(cfs = 2, spend, 0)) as spend'));
        $cookedFromScratch = $purchsesMetaQuery->first();
        if(!isset($cookedFromScratch->spend)){
            $result = null;
        } else {
            $result = round($cookedFromScratch->spend);
        }
        return $result;
    }

    // To fetch leakage from vendors data
    public static function leakageFromVendors($request, $costCenter, $date, $campusRollUp){
        if(!is_array($costCenter)){
            $costCenter = explode(',', $costCenter);
        }
        $checkAllLevel = app('check.allLevelFlag')($request);
        $checkCampusRollSummary = app('check.campusRollSummary')($request);
        $leakageQuery = DB::table('leakages');
       
        if($checkCampusRollSummary){
            $year = app('fiscal.year')($date);
            $leakageQuery->where('processing_year', $year);
        } else{
            $leakageQuery->whereIn('end_date', $date);
        }
        if($checkAllLevel){
            $leakageQuery->whereIn('unit', $costCenter);
        } else{
            $leakageQuery->whereIn('unit', $costCenter);
        }

        $leakageQuery->where('is_deleted', 0);
        $leakageQuery->sum('leakage_total_spend');

        $leakageFromVendors = $leakageQuery->get();
        if(!isset($leakageFromVendors->leakage_total_spend)){
            $result = null;
        } else {
            $result = round($leakageFromVendors->leakage_total_spend);
        }
        return $result;

    }

    // To fetch Farm to Fork GL Code graph data
    public static function farmToForkGLCodeData($request, $costCenters, $date, $campus_flag){
        $year = $request->year;
        $checkAllLevel = app('check.allLevelFlag')($request);
        $checkCampusRollSummary = app('check.campusRollSummary')($request);
        $constants = config('constants');
        
        // if(!is_array($costCenters)){
        //     $costCenters = explode(',', $costCenters);
        // }
        $glQuery = DB::table('gl_codes')->whereIn('exp_1', $constants['F2F_EXP_ARRAY_ONE']);
        
        if($checkCampusRollSummary){
            $glQuery->whereIn('unit_id', $costCenters);
            $glQuery->where('processing_year', $year);
        } else{
            $glQuery->whereIn('unit_id', $costCenters);
            $glQuery->whereIn('end_date', $date);
        }
        $amount = $glQuery->get()->sum('amount');
        $item1 = $amount;
        
        $glCodeQuery = DB::table('gl_codes');
        $glCodeQuery->whereIn('unit_id', $costCenters);
        
        if($checkAllLevel){
            $glCodeQuery->whereIn('exp_1', $constants['EXP_1']);
            $glCodeQuery->whereIn('end_date', $date);
        }
        if($checkCampusRollSummary){
            $glCodeQuery->whereIn('exp_1', '?');
            $glCodeQuery->whereIn('processing_year', '?');
        }
        if($campus_flag == 3){
            $glCodeQuery->whereIn('exp_1', '?');
            $glCodeQuery->whereIn('processing_year', '?');
        }
        if($campus_flag == 0){
            $glCodeQuery->whereIn('exp_1', '?');
            $glCodeQuery->whereIn('end_date', $date);
        }
        $produce = $glCodeQuery->where('exp_1', $constants['PRODUCE_GL_CODE'])
        ->where('created_at', $date)->first();

        $meat = $glCodeQuery->where('exp_1', $constants['MEAT_GL_CODE'])
        ->where('created_at', $date)->first();

        $cheese = $glCodeQuery->where('exp_1', $constants['CHEESE_GL_CODE'])
        ->where('created_at', $date)->first();

        $fluidDairy = $glCodeQuery->where('exp_1', $constants['FLUID_DAIRY_GL_CODE'])
        ->where('created_at', $date)->first();

        $seaFood = $glCodeQuery->where('exp_1', $constants['SEAFOOD_GL_CODE'])
        ->where('created_at', $date)->first();

        $sushi = $glCodeQuery->where('exp_1', $constants['SUSHI_GL_CODE'])
        ->where('created_at', $date)->first();

        $bakery = $glCodeQuery->where('exp_1', $constants['BAKERY_GL_CODE'])
        ->where('created_at', $date)->first();

        $artisanOther = $glCodeQuery->where('exp_1', $constants['ARTISAN_GL_CODE'])
        ->where('created_at', $date)->first();

        $coffee = $glCodeQuery->where('exp_1', $constants['COFFEE_GL_CODE'])
        ->where('created_at', $date)->first();

        $locallyCrafted = $glCodeQuery->where('exp_1', $constants['LOCALLY_CRAFTED_GL_CODE'])
        ->where('created_at', $date)->first();

        $dataArray = array(
            'produce' => array('key' => 'Produce', 'code' => $constants['PRODUCE_GL_CODE'], 'amount' =>  app('ff.chart')($produce->amount)),
            'meat' => array('key' => 'Meat/Eggs', 'code' => $constants['MEAT_GL_CODE'], 'amount' => app('ff.chart')($meat->amount)),
            'cheese' => array('key' => 'Cheese', 'code' => $constants['CHEESE_GL_CODE'], 'amount' => app('ff.chart')($cheese->amount)),
            'fluid_dairy' => array('key' => 'Fluid Dairy', 'code' => $constants['FLUID_DAIRY_GL_CODE'], 'amount' => app('ff.chart')($fluidDairy->amount)),
            'sea_food' => array('key' => 'Seafood', 'code' => $constants['SEAFOOD_GL_CODE'], 'amount' => app('ff.chart')($seaFood->amount)),
            'sushi' => array('key' => 'Sushi', 'code' => $constants['SUSHI_GL_CODE'], 'amount' => app('ff.chart')($sushi->amount)),
            'bakery' => array('key' => 'Bakery', 'code' => $constants['BAKERY_GL_CODE'], 'amount' => app('ff.chart')($bakery->amount)),
            'artisan_other' => array('key' => 'Artisan Other', 'code' => $constants['ARTISAN_GL_CODE'], 'amount' => app('ff.chart')($artisanOther->amount)),
            'coffee' => array('key' => 'Coffee/Tea', 'code' => $constants['COFFEE_GL_CODE'], 'amount' => app('ff.chart')($coffee->amount)),
            'locally_crafted' => array('key' => 'Locally Crafted', 'code' => $constants['LOCALLY_CRAFTED_GL_CODE'], 'amount' => app('ff.chart')($locallyCrafted->amount)),
        );
       
        return $dataArray;
    }
    
    static function getTicks($date, $costCenter, $campus_flag, $year){
        $tableName = 'trend_purchasing_' . intval($year);
        
        // Execute the query
        $results = DB::table($tableName)
            ->selectRaw('category, SUM(first_spend) as total_first_spend, SUM(second_spend) as total_second_spend')
            ->whereIn('cost_center', $costCenter)
            ->whereNotIn('category', ['pdv_total', 'seafood', 'meat'])
            ->groupBy('category')
            ->get();
        
        
        return $results;
    }
}
