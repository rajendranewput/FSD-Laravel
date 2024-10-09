<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class TrimmingTransportation extends Model
{
    use HasFactory;

    static function getTrimmingData($date, $costCenter, $campusFlag, $year, $fytd){
        $mfrItemCategoryCode = ['MCC-10001', 'MCC-10004', 'MCC-10007', 'MCC-10013', 'MCC-10014', 'MCC-10020', 
        'MCC-10021', 'MCC-10034', 'MCC-10036', 'MCC-10041', 'MCC-10048', 'MCC-10053', 
        'MCC-10054', 'MCC-10057', 'MCC-10059', 'MCC-10061', 'MCC-10066', 'MCC-10069'];

        try{
            $trimmQuery = DB::table('purchases_meta_'.$year)
            ->whereIn('financial_code', $costCenter)
            ->whereIn('mfrItem_parent_category_code', $mfrItemCategoryCode)
            ->whereNotIn('mfrItem_parent_category_code', ['MCC-10097']);

            // Conditional filter based on `$fytd`
            if ($fytd) {
                $trimmQuery->where('processing_year', $year);
            } else {
                $trimmQuery->whereIn('processing_month_date', $date);
            }
           
            $trimmData = $trimmQuery->select(DB::raw('SUM(IF(lcl = 2, spend, 0)) as spend'))->first();
            return $trimmData;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
