<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Export\FlavorFirst\MultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

/**
 * Flavor First Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class FlavorFirstController extends Controller
{
    //
    use DateHandlerTrait;

    /**
     * Export Flavor First Report
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with file download URL
     * 
     * @throws \Exception When export generation fails
     * 
     * @api {post} /flavor-first/export Export Flavor First Report
     * @apiName ExportFlavorFirst
     * @apiGroup FlavorFirst
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {String} data File download URL
     */
    public function export(Request $request){
        ini_set('memory_limit', '1024M');
        try{
            $year = $request->year;
            $campusFlag = $request->campus_flag;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            $type = $request->type;
            $teamName = $request->team_name;
            if($type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$teamName), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$teamName), true);
            }
            $filePath = 'flavor_first_report.xlsx';
            $url = Excel::store(new MultiSheetExport($year, $campusFlag, $date, $costCenter), $filePath, 'public');
            $url = Storage::disk('public')->url($filePath);
            
            return $this->successResponse($url, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
