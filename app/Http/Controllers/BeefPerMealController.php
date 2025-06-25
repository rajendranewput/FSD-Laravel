<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\BeefPerMeal;
use DateTime;

class BeefPerMealController extends Controller
{
    
    //
    use DateHandlerTrait;

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
            $data = BeefPerMeal::getBeefData($date, $costCenter, $request->campus_flag, $year, $fytd, $end_date);
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
