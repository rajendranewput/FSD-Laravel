<?php

namespace App\Http\Controllers;

use App\Http\Requests\widgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Cor;

class CorController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    public function CorData(widgetRequest $request){
        set_time_limit(120);
       
        $validated = $request->validated();
        try{
            $year = $request->year;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }

            $data = Cor::getCorData($date, $costCenter, $request->campus_flag, $year);
            $totalFirstItem = 0;
            $totalSecondItem = 0;
            foreach($data as $key => $value){
                if($value->cor == 1){
                    $totalFirstItem += $value->spend;
                }
                if(in_array($value->cor, [1,-1,2])){
                    $totalSecondItem += $value->spend;
                }
            }
            if(empty($totalFirstItem) || empty($totalSecondItem)){
                if(empty($totalFirstItem) && empty($totalSecondItem)){
                    $total = null;
                } else {
                    $total = 0;
                }
            } else {
                $total = round(($totalFirstItem/$totalSecondItem)*100);
            }

            $beef = $this->getCorValue($data, BEEF_CODE);
            $chiken = $this->getCorValue($data, CHICKEN_CODE);
            $turkey = $this->getCorValue($data, TURKEY_CODE);
            $pork = $this->getCorValue($data, PORK_CODE);
            $eggs = $this->getCorValue($data, EGGS_CODE);
            $dairy = $this->getCorValue($data, DAIRY_PRODUCT_CODE);
            $fish = $this->getCorValue($data, FISH_AND_SEEFOOD_CODE);

            $totalColor = $this->getColorThreshold($total, COR_SECTION);
            $beefColor = $this->getColorThreshold($beef, COR_SECTION);
            $chikenColor = $this->getColorThreshold($chiken, COR_SECTION);
            $turkeyColor = $this->getColorThreshold($turkey, COR_SECTION);
            $porkColor = $this->getColorThreshold($pork, COR_SECTION);
            $eggsColor = $this->getColorThreshold($eggs, COR_SECTION);
            $dairyColor = $this->getColorThreshold($dairy, COR_SECTION);
            $fishColor = $this->getColorThreshold($fish, COR_SECTION);
            
            $corResponse = array(
                'total_cor' => array('percentage' => $total, 'color_threshold' => $totalColor),
                'beef' => array('percentage' => $beef, 'color_threshold' => $beefColor),
                'chiken' => array('percentage' => $chiken, 'color_threshold' => $chikenColor),
                'turkey' => array('percentage' => $turkey, 'color_threshold' => $turkeyColor),
                'pork' => array('percentage' => $pork, 'color_threshold' => $porkColor),
                'eggs' => array('percentage' => $eggs, 'color_threshold' => $eggsColor),
                'dairy' => array('percentage' => $dairy, 'color_threshold' => $dairyColor),
                'fish' => array('percentage' => $fish, 'color_threshold' => $fishColor),
            );
            return response()->json([
                'status' => 'success',
                'data' => $corResponse,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
