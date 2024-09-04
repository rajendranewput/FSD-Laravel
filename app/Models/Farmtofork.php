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
}
