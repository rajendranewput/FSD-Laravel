<?php

namespace App\Http\Controllers;

use App\Http\Requests\widgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\DecreasingDeforestation;
use DateTime;

class DecreasingDeforestationController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    public function decreasingDeforestation(widgetRequest $request){
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
            
            // Imported Meat Spend
            $importedMeatData = DecreasingDeforestation::getImportedMeatData($date, $costCenter, $request->campus_flag, $year, $fytd, MEAT_MFR_CAT_CODE);
            $meatColorThreshold = $this->getColorThreshold($importedMeatData->spend, IMPORTED_MEAT);
            
            // Paper Purchases
            $paperData = DecreasingDeforestation::getPaperOrCoffeeData($date, $costCenter, $request->campus_flag, $year, $fytd, PAPER_MFR_CAT_CODE);
            $paperColorThreshold = $this->getColorThreshold($paperData, PAPER_PURCHASES);
            
            // Coffee Purchases
            $coffeeData = DecreasingDeforestation::getPaperOrCoffeeData($date, $costCenter, $request->campus_flag, $year, $fytd, COFFEE_MFR_CAT_CODE);
            $coffeeColorThreshold = $this->getColorThreshold($coffeeData, COFFEE_SPEND);
            
            return response()->json([
                'status' => 'success',
                'data' => [
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
                ],
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
