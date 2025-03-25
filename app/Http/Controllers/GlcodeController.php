<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Traits\DateHandlerTrait;
use App\Models\GlcodeModel;
use Illuminate\Support\Facades\Redis;

class GlcodeController extends Controller
{
    //
    use DateHandlerTrait;

    public function getGlcodeData(Request $request){
        $year = $request->year;
        $fytd = $request->fytd;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $data = GlcodeModel::getBargraphData($costCenter, $year, $date, $fytd);
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function getGlcodePopup(Request $request){
        $year = $request->year;
        $fytd = $request->fytd;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $data = GlcodeModel::getBargraphPopup($costCenter, $year, $date, $fytd, $request->code);
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }
}
