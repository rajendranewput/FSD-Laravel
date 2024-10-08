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
            $itemFirstQuery = DB::table('purchases_meta_'.$year)
            ->whereIn('financial_code', $costCenter)
            ->where('plant', 10);
            // Conditional filter based on `$fytd`
            if ($fytd) {
                $itemFirstQuery->where('processing_year', $year);
            } else {
                $itemFirstQuery->whereIn('processing_month_date', $date);
            }
            // Get the sum of `lbs`
            $itemFirstData = $itemFirstQuery->sum('lbs');
            
            $itemSecondQuery = DB::table('customer_counts')
            ->whereIn('unit_number', $costCenter);
            if ($fytd) {
                $itemSecondQuery->where('processing_year', $year);
            } else {
                $itemSecondQuery->whereIn('processing_month', $date);
            }
            $itemSecondData = $itemSecondQuery->sum('cust_count');
            if($fytd){
                $breakFast = DB::table('dashboard_aggregates_v2 as d')
                ->select(DB::raw('SUM(d.breakfast_percentage) as breakfast_percentage'))
                ->leftJoin('dashboard_fiscal_periods as p', 'd.processing_period', '=', 'p.end_date')
                ->leftJoin('cafes as c', 'c.cost_center', '=', 'd.cost_center')
                ->whereIn('d.cost_center', $costCenter)
                ->where('p.fiscal_year', $year)
                ->groupBy('d.cost_center','d.processing_period')
                ->get();
                $breakFastPer = 0;
                foreach ($breakFast as $item) {
                    $breakFastPer += $item->breakfast_percentage;
                }
            } else {
                $breakFast = DB::table('dashboard_aggregates_v2 as d')
                ->select(DB::raw('SUM(d.breakfast_percentage) as breakfast_percentage'))
                ->whereIn('d.cost_center', $costCenter)
                ->whereIn('processing_period', $end_date)
                ->first();
                $breakFastPer = $breakFast->breakfast_percentage;
               
            }
            if(empty($breakFastPer)){
                $breakFast = DB::table('cafes')
                ->select(DB::raw('SUM(breakfast_percentage)/count(cost_center) as breakfastper'))
                ->whereIn('cost_center', $costCenter)
                ->first();
                $breakFastPer = $breakFast->breakfastper;
            }
            $item1 = $itemFirstData;
            $item2 = $itemSecondData;
            
            if(empty($item1) || empty($item2) || empty($breakFastPer)){
                return null;
            } else {
                $breakFastPer = $breakFastPer/100;
                $b_m = $item1 / ($item2 * (1 - $breakFastPer));
                if($b_m < 1){
                    $beef_meal = number_format(ABS($item1 / ($item2 * (1 - $breakFastPer))), 2);
                } else {
                    $beef_meal = round(ABS($item1 / ($item2 * (1 - $breakFastPer))), 1);
                }
                return $beef_meal;
    
            }
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
