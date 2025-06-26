<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Farmtofork extends Model
{
    use HasFactory;

    static function getGlCodeData($date, $costCenter, $campusFlag, $year){
        $glCode = [PRODUCE_GL_CODE, MEAT_GL_CODE, CHEESE_GL_CODE, FLUID_DAIRY_GL_CODE, SEAFOOD_GL_CODE, SUSHI_GL_CODE, BAKERY_GL_CODE, ARTISAN_GL_CODE, COFFEE_GL_CODE, LOCALLY_CRAFTED_GL_CODE];
          
        $data = DB::table('gl_codes')
        ->select(DB::raw('SUM(amount) as amount'), 'exp_1')
        ->whereIn('unit_id', $costCenter)
        ->whereIn('exp_1', $glCode)
        ->whereIn('processing_year', $date)
        ->groupBy('exp_1')
        ->get();
        return $data;
    }

    static function farmToForkData($date, $costCenter, $campusFlag, $year, $type){
        // Get item 1 calculation
        $query = DB::table('gl_codes')
        ->select(DB::raw('SUM(amount) as amount'))
        ->whereIn('unit_id', $costCenter)
        ->whereIn('exp_1', F2F_EXP_ARRAY_ONE)
        // ->whereIn('processing_date', $date);
        ->whereIn('processing_date', $date);
        if($type == 'year'){
            $query->where('processing_year', $year);
        }
        $query->groupBy('exp_1');
        $item1 = $query->first();
        $item1 = $item1->amount;
        
        // Get item 2 calculation
        $query2 = DB::table('gl_codes')
        ->select(DB::raw('SUM(amount) as amount'))
        ->whereIn('unit_id', $costCenter)
        ->whereIn('exp_1', F2F_EXP_ARRAY_TWO)
        // ->whereIn('processing_date', $date);
        ->whereIn('processing_date', $date);
        if($type == 'year'){
            $query2->where('processing_year', $year);
        }
        $query2->groupBy('exp_1');
        $item2 = $query2->first();
        $item2 = $item2->amount;

        if (empty($item1) && empty($item2)) {
            $result = null;
        } elseif (empty($item1)) {
            $result = 0;
        } elseif (empty($item2)) {
            $result = null;
        } else {
            $result = round(ABS(($item1 / $item2) * 100), 1);
        }
        
        return $result;
    }
}
