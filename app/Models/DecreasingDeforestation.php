<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DecreasingDeforestation extends Model
{
    use HasFactory;

    static function getImportedMeatData($date, $costCenter, $campusFlag, $year, $fytd, $mfrItemCategoryCode){

        try{
            $importedMeatQuery = DB::table('purchases_meta_'.$year)
            ->whereIn('financial_code', $costCenter)
            ->whereIn('mfrItem_parent_category_code', $mfrItemCategoryCode)
            ->where('lcl', '2');

            if ($fytd) {
                $importedMeatQuery->where('processing_year', $year);
            } else {
                $importedMeatQuery->whereIn('processing_month_date', $date);
            }
           
            $importedMeatData = $importedMeatQuery->select(DB::raw('SUM(spend) as spend'))->first();
            return $importedMeatData;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    static function getPaperOrCoffeeData($date, $costCenter, $campusFlag, $year, $fytd, $mfrItemCategoryCode){

        try{
            // Base query common to both item1 and item2
            $baseQuery = DB::table('purchases_meta_'.$year)
            ->whereIn('financial_code', $costCenter)
            ->whereIn('mfrItem_parent_category_code', $mfrItemCategoryCode);

            if ($fytd) {
                $baseQuery->where('processing_year', $year);
            } else {
                $baseQuery->whereIn('processing_month_date', $date);
            }

            $item1Data = (clone $baseQuery)
                ->where('lcl', '1')
                ->select(DB::raw('SUM(spend) as spend'))
                ->first();

            $item2Data = (clone $baseQuery)
                ->whereIn('lcl', ['-1', '1', '2'])
                ->select(DB::raw('SUM(spend) as spend'))
                ->first();
                
            // Calculate the result
            if ($item2Data->spend != 0) {
                $paperCoffeeData = (($item1Data->spend / $item2Data->spend) * 100);
            } else {
                $paperCoffeeData = 0;
            }
          
            return $paperCoffeeData;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
