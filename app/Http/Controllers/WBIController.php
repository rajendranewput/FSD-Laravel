<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\WBI;
use DateTime;

class WBIController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    public function wbiData(Request $request){
        
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
            // Emphasize Bar Graph Data
            $wbi = new WBI();
            $wbiData = $wbi->getWBIData($date, $costCenter, $request->campus_flag, $year, $fytd);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'wbi_section_data' => $wbiData
                ],
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
