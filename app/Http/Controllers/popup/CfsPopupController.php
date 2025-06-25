<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Popup\CfsPopup;

/**
 * CFS (Cooked From Scratch) Popup Controller
 * 
 * @package App\Http\Controllers\Popup
 * @version 1.0
 */
class CfsPopupController extends Controller
{
    //
    use DateHandlerTrait;

    /**
     * Get CFS Popup Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with CFS popup data
     * 
     * @api {get} /cfs-popup Get CFS Popup Data
     * @apiName Index
     * @apiGroup CfsPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data CFS popup data with account information
     */
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
            return $this->successResponse($Cfs, 'success');
      //  }
    }

    /**
     * Get CFS Non-Compliant Popup Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with non-compliant CFS data
     * 
     * @api {get} /cfs-noncompliant-popup Get CFS Non-Compliant Popup Data
     * @apiName CfsNonCompliantPopup
     * @apiGroup CfsPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {Number} page Page number for pagination (default: 1)
     * @apiParam {Number} per_page Items per page (default: 10)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Non-compliant CFS data with pagination
     */
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
            return $this->successResponse($Cfs, 'success');
      //  }
    }

    /**
     * Get CFS Line Items Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with CFS line items data
     * 
     * @api {get} /get-cfs-line-item Get CFS Line Items Data
     * @apiName CfsLineItems
     * @apiGroup CfsPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} popup_type Type of popup for filtering
     * @apiParam {Number} page Page number for pagination (default: 1)
     * @apiParam {Number} per_page Items per page (default: 10)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Paginated CFS line items data
     */
    public function cfsLineItems(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $popupType = $request->popup_type;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $teamName = $request->team_name;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $Cfs = CfsPopup::getCfsLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage);
        return $this->successResponse($Cfs, 'success');

    }
    /**
     * Get CFS Line Items Details Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with CFS line items details data
     * 
     * @api {get} /get-cfs-line-item-details Get CFS Line Items Details Data
     * @apiName CfsLineItemsDetails
     * @apiGroup CfsPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} popup_type Type of popup for filtering
     * @apiParam {String} mfr_item_code Manufacturer item code
     * @apiParam {Number} page Page number for pagination (default: 1)
     * @apiParam {Number} per_page Items per page (default: 10)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Detailed CFS line items data
     */
    public function cfsLineItemsDetails(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $popupType = $request->popup_type;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $teamName = $request->team_name;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $mfrItemCode = $request->mfr_item_code;
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $Cfs = CfsPopup::getAccountCfsLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $mfrItemCode, $page, $perPage);
        return $this->successResponse($Cfs, 'success');

    }
}
