<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PurchasingTrait;
use DB;

class WellnessPlate extends Model
{
    use HasFactory, PurchasingTrait;

    public function getWellnessPlateData($date, $costCenter, $campusFlag, $year, $fytd){

        try{
            $query = DB::table('customer_counts')
            ->whereIn('unit_number', $costCenter);

            if ($fytd) {
                $query->where('processing_year', $year);
            } else {
                $query->whereIn('processing_month', $date);
            }

            $result = $query->select(DB::raw('SUM(cust_count) as cust_count'))->first();
            $item2 = $result ? $result->cust_count : null;

            $plantArray = ['31', '32', '33'];
            $produceData = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $produceMeta = $this->getColorThreshold($produceData, PRODUCE_DATA);
            
            $plantArray = ['26', '27'];
            $wholeGrain = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $wholeGrainMeta = $this->getColorThreshold($wholeGrain, WHOLE_GRAIN);
            $plantArray = ['18', '19'];
            $dairy = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $dairyMeta = $this->getColorThreshold($dairy, DAIRY);
            $plantArray = ['10', '11', '12', '13', '14', '15', '16', '17'];
            $animalProtiean = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $animalProtieanMeta = $this->getColorThreshold($animalProtiean, ANIMAL_PROTEIN);
            $plantArray = ['22', '23', '24', '25'];
            $plantProtiean = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $plantProtieanMeta = $this->getColorThreshold($plantProtiean, PLANT_PROTEIN);
            $plantArray = ['36'];
            $sugar = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $sugarMeta = $this->getColorThreshold($sugar, SUGAR);
            $plantArray = ['34'];
            $plantOil = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $plantOilMeta = $this->getColorThreshold($plantOil, PLANT_OIL);
            $produceArr = array(
                'value' => $produceData,
                'color' => $produceMeta,
                'circle_fill' => '',
            );
            $wholeGrainArr = array(
                'value' => $wholeGrain,
                'color' => $wholeGrainMeta,
                'circle_fill' => '',
            );
            $dairyArr = array(
                'value' => $dairy,
                'color' => $dairyMeta,
                'circle_fill' => 'Full',
            );
            $animalProtieanArr = array(
                'value' => $animalProtiean,
                'color' => $animalProtieanMeta,
                'circle_fill' => 'Full',
            );
            $plantProtieanArr = array(
                'value' => $plantProtiean,
                'color' => $plantProtieanMeta,
                'circle_fill' => '',
            );
            $sugarArr = array(
                'value' => $sugar,
                'color' => $sugarMeta,
                'circle_fill' => 'Full',
            );
            $plantOilArr = array(
                'value' => $plantOil,
                'color' => $plantOilMeta,
                'circle_fill' => 'Full',
            );
            $data = array(
                'produce_data' => $produceArr,
                'whole_grain' => $wholeGrainArr,
                'dairy' => $dairyArr,
                'animalProtiean' => $animalProtieanArr,
                'plantProtiean' => $plantProtieanArr,
                'sugar' => $sugarArr,
                'plantOil' => $plantOilArr,
            );
            return $data;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    static function getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2){
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
            if(isset($result->lbs)){
                $item1 = $result->lbs * 16;
            }
            $final_result = null;
            if(!isset($item1) || !isset($item2)){
                $final_result = null;
            } else {
                if(!empty($item2)){
                    $final_result = round($item1 / $item2, 1);
                }
            }
        
            return $final_result;

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}