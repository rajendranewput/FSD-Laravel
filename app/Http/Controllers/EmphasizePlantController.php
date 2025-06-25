<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\EmphasizePlant;
use DateTime;

/**
 * Emphasize Plant Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class EmphasizePlantController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get Emphasize Plant Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with emphasize plant data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /emphasize-plant-proteins Get Emphasize Plant Data
     * @apiName EmphasizePlant
     * @apiGroup EmphasizePlant
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} bar_chart_data Plant-forward purchasing data for charts
     */
    public function emphasizePlant(WidgetRequest $request){
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
            $emphasizeBarGraphData = EmphasizePlant::getEmphasizeBarGraphData($date, $costCenter, $request->campus_flag, $year, $fytd);
            
            return $this->successResponse([
                'bar_chart_data' => [
                    $emphasizeBarGraphData
                ]
            ], 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
