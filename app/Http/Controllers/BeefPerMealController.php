<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\BeefPerMeal;
use DateTime;

/**
 * Beef Per Meal Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class BeefPerMealController extends Controller
{
    //
    use DateHandlerTrait;

    /**
     * Get Beef Per Meal Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with beef per meal data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /beef-per-meal Get Beef Per Meal Data
     * @apiName BeefPerMeal
     * @apiGroup BeefPerMeal
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Beef per meal consumption data
     */
    public function beefPerMeal(WidgetRequest $request){
        $validated = $request->validated();
        
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
            $data = BeefPerMeal::getBeefData($date, $costCenter, $request->campus_flag, $year, $fytd, $endDate);
            return $this->successResponse($data, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
