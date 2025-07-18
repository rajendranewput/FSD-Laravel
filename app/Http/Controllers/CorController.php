<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Cor;

/**
 * COR Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class CorController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get COR Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with COR data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /cor-data Get COR Data
     * @apiName CorData
     * @apiGroup COR
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} total_cor Total COR data with percentage and color threshold
     * @apiSuccess {Object} beef Beef COR data with percentage and color threshold
     * @apiSuccess {Object} chiken Chicken COR data with percentage and color threshold
     * @apiSuccess {Object} turkey Turkey COR data with percentage and color threshold
     * @apiSuccess {Object} pork Pork COR data with percentage and color threshold
     * @apiSuccess {Object} eggs Eggs COR data with percentage and color threshold
     * @apiSuccess {Object} dairy Dairy COR data with percentage and color threshold
     * @apiSuccess {Object} fish Fish COR data with percentage and color threshold
     */
    public function CorData(WidgetRequest $request){
       
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
            return $this->successResponse($corResponse, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
