<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\DecreasingDeforestation;
use DateTime;

class DecreasingDeforestationController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    public function decreasingDeforestation(Request $request){
        
        try{
            $year = $request->year;
            $fytd = $request->fytd;
            if (strpos($request->end_date, ',') !== false) {
                $request->end_date = explode(',', $request->end_date);
            }
            if(is_array($request->end_date)){
                foreach($request->end_date as $dates){
                    $d = new DateTime($dates);
                    $end_date[] = $date->format('n/d/Y');
                }
            } else {
                $d = new DateTime($request->end_date);
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
