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
            $produceMeta = $this->getColorThreshold($produceData, 6.5, TRUE, TRUE);
            
            $plantArray = ['26', '27'];
            $wholeGrain = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $wholeGrainMeta = $this->getColorThreshold($wholeGrain, 2.7, TRUE, TRUE);
            $plantArray = ['18', '19'];
            $dairy = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $dairyMeta = $this->getColorThreshold($dairy, 2.9, FALSE, FALSE);
            $plantArray = ['10', '11', '12', '13', '14', '15', '16', '17'];
            $animalProtiean = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $animalProtieanMeta = $this->getColorThreshold($animalProtiean, 2.5, FALSE, FALSE);
            $plantArray = ['22', '23', '24', '25'];
            $plantProtiean = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $plantProtieanMeta = $this->getColorThreshold($plantProtiean, 1.5, TRUE, TRUE);
            $plantArray = ['36'];
            $sugar = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $sugarMeta = $this->getColorThreshold($sugar, 0.4, FALSE, FALSE);
            $plantArray = ['34'];
            $plantOil = self::getPlateData($date, $costCenter, $campusFlag, $year, $fytd, $plantArray, $item2);
            $plantOilMeta = $this->getColorThreshold($plantOil, 0.6, FALSE, FALSE);
            $produceArr = array(
                'value' => $produceData,
                'color' => $produceMeta['color'],
                'circle_fill' => $produceMeta['circle_fill'],
            );
            $wholeGrainArr = array(
                'value' => $wholeGrain,
                'color' => $wholeGrainMeta['color'],
                'circle_fill' => $wholeGrainMeta['circle_fill'],
            );
            $dairyArr = array(
                'value' => $dairy,
                'color' => $dairyMeta['color'],
                'circle_fill' => $dairyMeta['circle_fill'],
            );
            $animalProtieanArr = array(
                'value' => $animalProtiean,
                'color' => $animalProtieanMeta['color'],
                'circle_fill' => $animalProtieanMeta['circle_fill'],
            );
            $plantProtieanArr = array(
                'value' => $plantProtiean,
                'color' => $plantProtieanMeta['color'],
                'circle_fill' => $plantProtieanMeta['circle_fill'],
            );
            $sugarArr = array(
                'value' => $sugar,
                'color' => $sugarMeta['color'],
                'circle_fill' => $sugarMeta['circle_fill'],
            );
            $plantOilArr = array(
                'value' => $plantOil,
                'color' => $plantOilMeta['color'],
                'circle_fill' => $plantOilMeta['circle_fill'],
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
    public function getColorThreshold($calculation, $goal, $is_cirlce_fill, $is_goal_greater){
        $color = '';
        $circle_fill = 0;
        if(isset($calculation)){
            if($is_cirlce_fill == TRUE){
                $circle_fill = round($calculation / $goal *100, 1);
            }
            if($is_goal_greater){
                if($calculation >= $goal){
                    $color = '#63BF87';
                    $circle_fill = 'Full';
                } else {
                    $color = '#E5E56B';
                }
            } else {
                if($calculation > $goal){
                    $color = '#E5E56B';
                } else {
                    $color = '#63BF87';
                    $circle_fill = 'Full';
                }
            }
        } else {
            $circle_fill = 'Full';
        }
        return array('color' => $color, 'circle_fill' => $circle_fill);
    }
}