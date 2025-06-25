<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\WBI;
use DateTime;

/**
 * WBI (Wellness, Balance, Innovation) Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class WBIController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get WBI Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with WBI data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /wbi Get WBI Data
     * @apiName WbiData
     * @apiGroup WBI
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} wbi_section_data WBI metrics and analysis data
     */
    public function wbiData(Request $request){
        
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
                    $end_date[] = $d->format('n/d/Y');
                }
            } else {
                $d = new DateTime($dates);
                $end_date[] = $d->format('n/d/Y');
            }
            $date = $this->handleDates($request->end_date, $request->campus_flag);
           
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            // Emphasize Bar Graph Data
            $wbi = new WBI();
            $wbiData = $wbi->getWBIData($date, $costCenter, $request->campus_flag, $year, $fytd);
            
            return $this->successResponse([
                'wbi_section_data' => $wbiData
            ], 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
