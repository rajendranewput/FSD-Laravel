<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Popup\CfsPopup;

class CfsPopupController extends Controller
{
    //
    use DateHandlerTrait;

    public function index(Request $request)
    {
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $teamName = $request->team_name;
        // $record = json_decode(Redis::get($type.'_cfs_'.$date[0]), true);
        // if(empty($record)){
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $Cfs = CfsPopup::getAccountCfs($costCenter, $date, $campusFlag, $type, $teamName);
            return response()->json([
                'status' => 'success',
                'data' => $Cfs,
            ], 200);
      //  }
    }

    public function cfsNonCompliantPopup(Request $request)
    {
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $teamName = $request->team_name;
        
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
           
            $Cfs = CfsPopup::getNonComplaintCfs($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage);
            return response()->json([
                'status' => 'success',
                'data' => $Cfs,
            ], 200);
      //  }
    }
}
