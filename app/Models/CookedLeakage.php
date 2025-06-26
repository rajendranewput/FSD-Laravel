<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CookedLeakage extends Model
{
    use HasFactory;

    // To fetch cooked from scratch data
    static function cookedFromScratch($date, $costCenter, $campusFlag, $year){
       
        $cookedFromScratch = DB::table('purchases_meta_'.$year)     
            ->select(DB::raw('SUM(IF(cfs = 2, spend, 0)) as spend'))
            ->where('processing_year', $year)
            ->whereIn('processing_month_date', $date)
            ->whereIn('financial_code', $costCenter)
            ->first();
        if(!isset($cookedFromScratch->spend)){
            $result = null;
        } else {
            $result = round($cookedFromScratch->spend);
        }
        return $result;
    }

    // To fetch leakage from vendors data
    static function leakageFromVendors($date, $costCenter, $campusFlag, $year){

        $leakageFromVendors = DB::table('leakages')
        ->select(DB::raw('SUM(leakage_total_spend) as leakage_total_spend'))
        ->where('processing_year', $year)
        ->whereIn('end_date', $date)
        ->whereIn('unit', $costCenter)
        ->where('is_deleted', 0)
        ->first();
        if(!isset($leakageFromVendors->leakage_total_spend)){
            $result = null;
        } else {
            $result = round($leakageFromVendors->leakage_total_spend);
        }
        return $result;

    }
}
