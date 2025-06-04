<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\TrimmingTransportation;
use DateTime;

class TrimmingTransportationController extends Controller
{
    //
    use DateHandlerTrait;

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
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
