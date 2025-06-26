<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\DecreasingDeforestation;
use DateTime;

/**
 * Decreasing Deforestation Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class DecreasingDeforestationController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get Decreasing Deforestation Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with deforestation reduction data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /decreasing-deforestation Get Decreasing Deforestation Data
     * @apiName DecreasingDeforestation
     * @apiGroup DecreasingDeforestation
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} restricted_imported_meat Imported meat data with color threshold
     * @apiSuccess {Object} paper_purchases Paper purchasing data with color threshold
     * @apiSuccess {Object} coffee_spend Coffee spending data with color threshold
     */
    public function decreasingDeforestation(WidgetRequest $request){
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
            
            // Imported Meat Spend
            $importedMeatData = DecreasingDeforestation::getImportedMeatData($date, $costCenter, $request->campus_flag, $year, $fytd, MEAT_MFR_CAT_CODE);
            $meatColorThreshold = $this->getColorThreshold($importedMeatData->spend, IMPORTED_MEAT);
            
            // Paper Purchases
            $paperData = DecreasingDeforestation::getPaperOrCoffeeData($date, $costCenter, $request->campus_flag, $year, $fytd, PAPER_MFR_CAT_CODE);
            $paperColorThreshold = $this->getColorThreshold($paperData, PAPER_PURCHASES);
            
            // Coffee Purchases
            $coffeeData = DecreasingDeforestation::getPaperOrCoffeeData($date, $costCenter, $request->campus_flag, $year, $fytd, COFFEE_MFR_CAT_CODE);
            $coffeeColorThreshold = $this->getColorThreshold($coffeeData, COFFEE_SPEND);
            
            return $this->successResponse([
                'restricted_imported_meat' => [
                    'spend' => $importedMeatData->spend,
                    'color_threshold' => $meatColorThreshold
                ],
                'paper_purchases' => [
                    'spend' => $paperData,
                    'color_threshold' => $paperColorThreshold
                ],
                'coffee_spend' => [
                    'spend' => $coffeeData,
                    'color_threshold' => $coffeeColorThreshold
                ]
            ], 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
