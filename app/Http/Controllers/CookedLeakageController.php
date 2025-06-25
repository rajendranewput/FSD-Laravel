<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use App\Models\CookedLeakage;
use Illuminate\Support\Facades\Redis;

/**
 * Cooked Leakage Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class CookedLeakageController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get Cooked and Leakage Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with cooked and leakage data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /cooked-leakage Get Cooked and Leakage Data
     * @apiName CookedLeakageData
     * @apiGroup CookedLeakage
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} cookedFromScratch Cooked-from-scratch data with color threshold
     * @apiSuccess {Object} leakageFromVendors Leakage data with color threshold
     */
    public function cookedLeakageData(WidgetRequest $request){
        
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
            
            return $this->successResponse($data, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
    
}
