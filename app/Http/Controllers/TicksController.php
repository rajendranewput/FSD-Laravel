<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Purchasing;
use DateTime;

/**
 * Ticks Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class TicksController extends Controller
{
    use DateHandlerTrait;

    /**
     * Get Ticks Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with ticks data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /ticks Get Ticks Data
     * @apiName Ticks
     * @apiGroup Ticks
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     */
    public function ticks(Request $request){
        try{
            $year = $request->year;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }

            $purchasing = Purchasing::getTicks($date, $costCenter, $request->campus_flag, $year);
            $greenTallies = 0;
        $redTallies = 0;

        foreach($purchasing as $key => $value){
            if($value->category == 'leakage'){
                $result = round($value->total_first_spend);
                if($value->total_first_spend <= 0){
                    $greenTallies += 1;
                } else {
                    $redTallies += 1;
                } 
            } else {
                if(empty($value->total_first_spend) || empty($value->total_second_spend)){
                    if(empty($value->total_first_spend) && empty($value->total_second_spend)){
                        $result = null;
                    } else {
                        $result = 0;
                    }
                } else {
                    $result = round(($value->total_first_spend/$value->total_second_spend)*100);
                }
                if($value->category == 'gl_code'){
                    if($result >= FF_COLOR_DIVIDE_VALUE){
                        $greenTallies += 1;
                    } else {
                        $redTallies += 1;
                    } 
                } else {
                    if($result >= COR_COLOR_DIVIDE_VALUE){
                        $greenTallies += 1;
                    } else {
                        $redTallies += 1;
                    }
                }
            }
        }
        $tickPercentage = round(($greenTallies/($greenTallies+$redTallies))*100);
        $totalGreen = round(14 * $tickPercentage/100);
        $totalRed = round(14 - $totalGreen);
        $purchasing = array(
            'green' => $totalGreen,
            'red' => $totalRed
        );
        $climateChange = array(
            'green' => 0,
            'red' => 0
        );
        $plantForward = array(
            'green' => 0,
            'red' => 0
        );
        $wellness = array(
            'green' => 0,
            'red' => 0
        );
        $ticksData = array(
            'purchasing' => $purchasing,
            'climate_change' => $climateChange,
            'plant_forward' => $plantForward,
            'wellness' => $wellness
        );
        return $this->successResponse($ticksData, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
