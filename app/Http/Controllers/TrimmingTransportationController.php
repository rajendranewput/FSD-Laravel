<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\TrimmingTransportation;
use DateTime;

/**
 * Trimming Transportation Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class TrimmingTransportationController extends Controller
{
    //
    use DateHandlerTrait;

    /**
     * Get Trimming Transportation Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with trimming transportation data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /trimming-transportation Get Trimming Transportation Data
     * @apiName TrimmingTransportation
     * @apiGroup TrimmingTransportation
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Trimming and transportation efficiency data
     */
    public function trimmingTransportation(Request $request){
        
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
            $data = TrimmingTransportation::getTrimmingData($date, $costCenter, $request->campus_flag, $year, $fytd);
            return $this->successResponse($data, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
