<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class BeefPerMeal extends Model
{
    use HasFactory;

    static function getBeefData($date, $costCenter, $campusFlag, $year, $fytd, $end_date){
        //Item 1
        try{
            $itemFirst = DB::table('purchases_meta_'.$year)
            ->select(DB::raw('SUM(lbs) as lbs'))
            ->whereIn('financial_code', $costCenter);
            if($fytd){
                $itemFirst->where('processing_year', $year);
            } else {
                $itemFirst->whereIn('processing_month_date', $date);
            }
            $itemFirst->where('plant', 10);
            $itemFirstData = $itemFirst->first();
            
            $itemSecond = DB::table('customer_counts')
            ->select(DB::raw('SUM(cust_count) as cust_count'))
            ->whereIn('unit_number', $costCenter);
            if($fytd){
                $itemSecond->where('processing_year', $year);
            } else {
                $itemSecond->whereIn('processing_month', $date);
            }
            $itemSecondData = $itemSecond->first();
            if($fytd){
                $breakFast = DB::table('dashboard_aggregates_v2 as d')
                ->select(DB::raw('SUM(d.breakfast_percentage) as breakfast_percentage'))
                ->leftJoin('dashboard_fiscal_periods as p', 'd.processing_period', '=', 'p.end_date')
                ->leftJoin('cafes as c', 'c.cost_center', '=', 'd.cost_center')
                ->whereIn('d.cost_center', $costCenter)
                ->where('p.fiscal_year', $year)
                ->groupBy('d.cost_center','d.processing_period')
                ->get();
                $breakfast_per = 0;
                foreach ($breakFast as $item) {
                    $breakfast_per += $item->breakfast_percentage;
                }
            } else {
                $breakFast = DB::table('dashboard_aggregates_v2 as d')
                ->select(DB::raw('SUM(d.breakfast_percentage) as breakfast_percentage'))
                ->whereIn('d.cost_center', $costCenter)
                ->whereIn('processing_period', $end_date)
                ->first();
                $breakfast_per = $breakFast->breakfast_percentage;
               
            }
            if(empty($breakfast_per)){
                $breakFast = DB::table('cafes')
                ->select(DB::raw('SUM(breakfast_percentage)/count(cost_center) as breakfastper'))
                ->whereIn('cost_center', $costCenter)
                ->first();
                $breakfast_per = $breakFast->breakfastper;
            }
            $item1 = $itemFirstData->lbs;
            $item2 = $itemSecondData->cust_count;
            
            if(empty($item1) || empty($item2) || empty($breakfast_per)){
                return null;
            } else {
                $breakfast_per = $breakfast_per/100;
                $b_m = $item1 / ($item2 * (1 - $breakfast_per));
                if($b_m < 1){
                    $beef_meal = number_format(ABS($item1 / ($item2 * (1 - $breakfast_per))), 2);
                } else {
                    $beef_meal = round(ABS($item1 / ($item2 * (1 - $breakfast_per))), 1);
                }
                return $beef_meal;
    
            }
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
