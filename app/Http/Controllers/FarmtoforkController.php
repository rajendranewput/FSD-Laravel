<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use App\Models\Farmtofork;
use App\Models\Purchasing;
use Illuminate\Support\Facades\Redis;

/**
 * Farm to Fork Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class FarmtoforkController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get Farm to Fork GL Code Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with farm to fork purchasing data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /farm-fork-spend-data Get Farm to Fork Purchasing Data
     * @apiName FarmToForkPurchasingData
     * @apiGroup FarmToFork
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Farm to fork purchasing data
     */
    public function farmToForkPurchasingData(WidgetRequest $request){
        $validated = $request->validated();
        try{
            $year = $request->year;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            
            $farmToForkYear = Farmtofork::farmToForkData($date, $costCenter, $request->campus_flag, $year, 'year');
            $colorThreshold = $this->getColorThreshold($farmToForkYear, FARM_FORK_SECTION);
            $percentage = ($farmToForkYear/FF_FULL_CIRCLE_VALUE)*100;
            $yearData = array(
                'percentage' => $percentage,
                'display_value' => $farmToForkYear,
                'color_threshold' => $colorThreshold
            );

            $farmToForkPeriod = Farmtofork::farmToForkData($date, $costCenter, $request->campus_flag, $year, 'period');
            $colorThresholdPeriod = $this->getColorThreshold($farmToForkPeriod, FARM_FORK_SECTION);
            $percentagePeriod = ($farmToForkPeriod/FF_FULL_CIRCLE_VALUE)*100;
            $periodData = array(
                'percentage' => $percentagePeriod,
                'display_value' => $farmToForkPeriod,
                'color_threshold' => $colorThresholdPeriod
            );
            $finalData = array(
                'farmToForkPeriodData' => $periodData,
                'farmToForkYearData' => $yearData
            );

            return $this->successResponse($finalData, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
