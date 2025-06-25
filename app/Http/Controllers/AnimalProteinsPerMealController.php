<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\AnimalProteinsPerMeal;
use DateTime;

/**
 * Animal Proteins Per Meal Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class AnimalProteinsPerMealController extends Controller
{
    
    use DateHandlerTrait;

    /**
     * Get Animal Proteins Per Meal Data
     * 
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with animal proteins per meal data
     * 
     * @throws \Exception When data processing fails
     * 
     * @api {get} /animal-proteins-per-meal Get Animal Proteins Per Meal Data
     * @apiName AnimalProteinsPerMeal
     * @apiGroup AnimalProteinsPerMeal
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Animal protein consumption per meal data
     */
    public function animalProteinsPerMeal(WidgetRequest $request){
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
            $data = AnimalProteinsPerMeal::getAnimalProteinsPerMealData($date, $costCenter, $request->campus_flag, $year, $fytd, $end_date);
            
            return $this->successResponse($data, 'Success');
        } catch(\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
