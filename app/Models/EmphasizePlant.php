<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmphasizePlant extends Model
{
    use HasFactory;

    static function getEmphasizeBarGraphData($date, $costCenter, $campusFlag, $year, $fytd){

        try{
            $plantArray = ['10', '11', '12'];
            $beefLambPork = self::getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray);
            $plantArray = ['15', '16', '17'];
            $poultry = self::getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray);
            $plantArray = ['18', '19'];
            $dairy = self::getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray);
            $plantArray = ['20'];
            $eggs = self::getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray);
            $plantArray = ['14'];
            $fishSeafood = self::getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray);
            $plantArray = ['22', '23', '24', '25'];
            $plantProtien = self::getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray);

            $data = array(
                'beef_lamb_pork' => $beefLambPork,
                'poultry' => $poultry,
                'dairy' => $dairy,
                'eggs' => $eggs,
                'fish_seafood' => $fishSeafood,
                'plant_protien' => $plantProtien
            );
            return $data;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    static function getEmphasizeBarLbsSum($date, $costCenter, $campusFlag, $year, $fytd, $plantArray){
        try{
            $query = DB::table('purchases_'.$year)
            ->whereIn('financial_code', $costCenter)
            ->whereIn('plant', $plantArray);
            if ($fytd) {
                $query->where('processing_year', $year);
            } else {
                $query->whereIn('processing_month_date', $date);
            }

            $result = $query->select(DB::raw('SUM(lbs) as lbs'))->first();
            return $result ? abs(round($result->lbs)) : null;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}