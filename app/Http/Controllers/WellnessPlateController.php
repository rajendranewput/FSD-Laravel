<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\WellnessPlate;
use DateTime;

/**
 * Wellness Plate Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class WellnessPlateController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get Wellness Plate Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with wellness plate data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /wellness-plate Get Wellness Plate Data
     * @apiName WellnessPlate
     * @apiGroup WellnessPlate
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Wellness plate nutritional data
     */
    public function wellnessPlate(Request $request){
        
        try{
            $year = $request->year;
            $fytd = $request->fytd;
            $dates = $request->end_date;
            if (strpos($dates, ',') !== false) {
                $dates = explode(',', $dates);
            }
            if(is_array($dates)){
                foreach($dates as $day){
                    $d = new DateTime($day);
                    $endDate[] = $d->format('n/d/Y');
                }
            } else {
                $d = new DateTime($dates);
                $endDate[] = $d->format('n/d/Y');
            }
            $date = $this->handleDates($request->end_date, $request->campus_flag);
           
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            // Wellness Plate Data
            $wellnessPlate = new WellnessPlate();
            $wellnessPlateData = $wellnessPlate->getWellnessPlateData($date, $costCenter, $request->campus_flag, $year, $fytd);
            
            return $this->successResponse($wellnessPlateData, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
