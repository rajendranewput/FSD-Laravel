<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\widgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use App\Models\CookedLeakage;
use Illuminate\Support\Facades\Redis;

class CookedLeakageController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /*** get cooked and leakage data*/
    public function cookedLeakageData(widgetRequest $request){
        
        $validated = $request->validated();
        
        try{
            $year = $request->year;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $cookedFromScratch = CookedLeakage::cookedFromScratch($date, $costCenter, $request->campus_flag, $year);
            $leakageFromVendors = CookedLeakage::leakageFromVendors($date, $costCenter, $request->campus_flag, $year);
            $cookedColor = $this->getColorThreshold($cookedFromScratch, COOKED_LEAKAGE_SECTION);
            $leakageColor = $this->getColorThreshold($leakageFromVendors, COOKED_LEAKAGE_SECTION);
            
            $data = array(
                'cookedFromScratch' => array(
                    'amount' => $cookedFromScratch,
                    'color_threshold' => $cookedColor,
                ),
                'leakageFromVendors' => array(
                    'amount' => $leakageFromVendors,
                    'color_threshold' => $leakageColor,
                ),
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}
