<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cor extends Model
{
    use HasFactory;

    static function getCorData($date, $costCenter, $campusFlag, $year){
        $data = DB::table('purchases_'.$year)
        ->select(DB::raw('SUM(spend) as spend'), 'cor', 'mfrItem_parent_category_code')
        ->whereIn('financial_code', $costCenter)
        ->whereIn('processing_month_date', $date)
        ->whereIn('mfrItem_parent_category_code', [BEEF_CODE, CHICKEN_CODE, TURKEY_CODE, PORK_CODE, EGGS_CODE, DAIRY_PRODUCT_CODE, FISH_AND_SEEFOOD_CODE])
        ->whereIn('cor', ['-1', '1', '2'])
        ->groupBy('cor')
        ->groupBy('mfrItem_parent_category_code')
        ->get();
       return $data;
    }
}
