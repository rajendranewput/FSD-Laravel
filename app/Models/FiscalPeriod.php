<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class FiscalPeriod extends Model
{
    use HasFactory;

    static function getyears(){
        $data = DB::table('dashboard_fiscal_periods')
        ->select('fiscal_year')
        ->where('fiscal_year', '!=', '2015')
        ->groupBy('fiscal_year')
        ->get();
        return $data;
    }
    static function getPeriod($cost_center, $year, $latest){
        
        $todayDate = date('Y-m-01');
        $data = DB::table('dashboard_fiscal_periods')
        ->select('fiscal_period', 'end_date')
        ->where('fiscal_year', $year)
        ->where(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01")'), '<=', $todayDate)
        ->get();
        return $data;
    }
}
