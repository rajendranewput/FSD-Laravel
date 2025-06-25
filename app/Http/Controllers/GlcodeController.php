<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Traits\DateHandlerTrait;
use App\Models\GlcodeModel;
use Illuminate\Support\Facades\Redis;

/**
 * GL Code Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class GlcodeController extends Controller
{
    //
    use DateHandlerTrait;

    /**
     * Get GL Code Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with GL code data
     * 
     * @api {get} /get-gl-graph Get GL Code Data
     * @apiName GetGlcodeData
     * @apiGroup GLCode
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data GL code spending data for bar graphs
     */
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
        return $this->successResponse($data, 'success');
    }

    /**
     * Get GL Code Popup Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with GL code popup data
     * 
     * @api {get} /get-gl-graph-popup Get GL Code Popup Data
     * @apiName GetGlcodePopup
     * @apiGroup GLCode
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} fytd Fiscal year to date flag
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} code Specific GL code for detailed analysis
     * @apiSuccess {Object} data Detailed GL code popup data
     */
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
        return $this->successResponse($data, 'success');
    }
}
