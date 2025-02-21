<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class AnimalProteinsPerMeal extends Model
{
    use HasFactory;

    static function getAnimalProteinsPerMealData($date, $costCenter, $campusFlag, $year, $fytd, $end_date){
        //Item 1
        try{
            $itemFirstQuery = DB::table('purchases_meta_'.$year)
            ->whereIn('financial_code', $costCenter)
            ->whereIn('plant', [10, 11, 12, 13, 14, 15, 16, 17]);
            // Conditional filter based on `$fytd`
            if ($fytd) {
                $itemFirstQuery->where('processing_year', $year);
            } else {
                $itemFirstQuery->whereIn('processing_month_date', $date);
            }
            // Get the sum of `lbs`
            $itemFirstData = ($itemFirstQuery->sum('lbs')) * 16;
            
            $itemSecondQuery = DB::table('customer_counts')
            ->whereIn('unit_number', $costCenter);
            if ($fytd) {
                $itemSecondQuery->where('processing_year', $year);
            } else {
                $itemSecondQuery->whereIn('processing_month', $date);
            }
            $itemSecondData = $itemSecondQuery->sum('cust_count');
        
            if(empty($itemFirstData) || empty($itemSecondData)){
                $animal_protiean = null;
            } else {
                $animal_protieans = $itemFirstData / $itemSecondData;
                if($animal_protieans < 1){
                    $animal_protiean = number_format($itemFirstData / $itemSecondData, 2);
                } else {
                    $animal_protiean = round($itemFirstData / $itemSecondData, 1);
                }
            }
            return $animal_protiean;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
