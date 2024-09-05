<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Farmtofork extends Model
{
    use HasFactory;

    static function getGlCodeData($date, $costCenter, $campus_flag, $year){
        $glCode = [PRODUCE_GL_CODE, MEAT_GL_CODE, CHEESE_GL_CODE, FLUID_DAIRY_GL_CODE, SEAFOOD_GL_CODE, SUSHI_GL_CODE, BAKERY_GL_CODE, ARTISAN_GL_CODE, COFFEE_GL_CODE, LOCALLY_CRAFTED_GL_CODE];
          
        $data = DB::table('gl_codes')
        ->select(DB::raw('SUM(amount) as amount'), 'exp_1')
        ->whereIn('unit_id', $costCenter)
        ->whereIn('exp_1', $glCode)
        ->whereIn('processing_month', $date)
        ->groupBy('exp_1')
        ->get();
        return $data;
    }

    static function farmToForkData($date, $costCenter, $campus_flag, $year){
        // Get item 1 calculation
        $data1 = DB::table('gl_codes')
        ->select(DB::raw('SUM(amount) as amount'), 'exp_1')
        ->whereIn('unit_id', $costCenter)
        ->whereIn('exp_1', F2F_EXP_ARRAY_ONE)
        ->whereIn('end_date', $date)
        ->groupBy('exp_1')
        ->first();
        $item1 = $data1;
        
        // Get item 2 calculation
        $data2 = DB::table('gl_codes')
        ->select(DB::raw('SUM(amount) as amount'), 'exp_1')
        ->whereIn('unit_id', $costCenter)
        ->whereIn('exp_1', F2F_EXP_ARRAY_TWO)
        ->whereIn('end_date', $date)
        ->whereIn('processing_year', [$year])
        ->groupBy('exp_1')
        ->first();
        $item2 = $data2;

        if (empty($item1) && empty($item2)) {
            $result = null;
        } elseif (empty($item1)) {
            $result = 0;
        } elseif (empty($item2)) {
            $result = null;
        } else {
            $result = round(abs($item1 / $item2) * 100, 1);
        }
        return $result;
    }
}
