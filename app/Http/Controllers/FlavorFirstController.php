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
            $year = '2024';
            $campusFlag = 10;
            $date = array('2024-02-01');
            $type = 'summary';
            $team_name = 'A00000';
            if($type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$team_name), true);
            }
            $filePath = 'flavor_first_report.xlsx';
            $url = Excel::store(new MultiSheetExport($year, $campusFlag, $date, $costCenter), $filePath, 'public');
            $url = Storage::disk('public')->url($filePath);
            echo $url;
            die;
            // Return the URL
            return response()->json(['url' => $url], 200);
            //return Excel::download(new MultiSheetExport($year, $campusFlag, $date, $costCenter), 'multi_sheet_export.xlsx');
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
