<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Export\FlavorFirst\MultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;


class FlavorFirstController extends Controller
{
    //
    use DateHandlerTrait;

    public function export(Request $request){
        ini_set('memory_limit', '1024M');
        try{
            $year = $request->year;
            $campusFlag = $request->campus_flag;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            $type = $request->type;
            $team_name = $request->team_name;
            if($type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$team_name), true);
            }
            $filePath = 'flavor_first_report.xlsx';
            $url = Excel::store(new MultiSheetExport($year, $campusFlag, $date, $costCenter), $filePath, 'public');
            $url = Storage::disk('public')->url($filePath);
            
            return response()->json([
                'status' => 'success',
                'data' => $url,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
