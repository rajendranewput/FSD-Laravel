<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Popup\LeakagePopup;


class LeakagePopupController extends Controller
{
    use DateHandlerTrait;

    public function index(Request $request)
    {
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $date = $this->handleDates($request->end_date, $request->campus_flag, true);
        $teamName = $request->team_name;
        // $record = json_decode(Redis::get($type.'_cfs_'.$date[0]), true);
        // if(empty($record)){
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $leakage = LeakagePopup::getAccountLeakage($costCenter, $date, $campusFlag, $type, $teamName);
            return response()->json([
                'status' => 'success',
                'data' => $leakage,
            ], 200);
      //  }
    }

    public function leakageNonCompliantPopup(Request $request)
    {
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $date = $this->handleDates($request->end_date, $request->campus_flag, true);
        $teamName = $request->team_name;
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $leakage = LeakagePopup::getNonComplaintData($costCenter, $date, $campusFlag, $type, $teamName, $page, $perPage);
        return response()->json([
            'status' => 'success',
            'data' => $leakage,
        ], 200);
    }
}
