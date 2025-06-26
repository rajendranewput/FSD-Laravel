<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Popup\LeakagePopup;


/**
 * Leakage Popup Controller
 *
 * @package App\Http\Controllers\Popup
 * @version 1.0
 */
class LeakagePopupController extends Controller
{
    use DateHandlerTrait;

    /**
     * Get Leakage Popup Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with leakage popup data
     * 
     * @api {get} /leakage-popup Get Leakage Popup Data
     * @apiName Index
     * @apiGroup LeakagePopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiSuccess {Object} data Leakage popup data with account information
     */
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
            return $this->successResponse($leakage, 'success');
      //  }
    }

    /**
     * Get Leakage Non-Compliant Popup Data
*
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with non-compliant leakage data
     * 
     * @api {get} /leakage-noncompliant-popup Get Leakage Non-Compliant Popup Data
     * @apiName LeakageNonCompliantPopup
     * @apiGroup LeakagePopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiParam {Number} page Page number for pagination
     * @apiParam {Number} per_page Items per page for pagination
     * @apiSuccess {Object} data Non-compliant leakage accounts data
     */
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
        $leakage = LeakagePopup::getNonComplaintData($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage);
        return $this->successResponse($leakage, 'success');
    }

    /**
     * Get Leakage Line Items Data
     
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with leakage line items data
     * 
     * @api {get} /get-leakage-line-item Get Leakage Line Items Data
     * @apiName LeakageLineItems
     * @apiGroup LeakagePopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} popup_type Type of popup for filtering
     * @apiParam {Number} page Page number for pagination
     * @apiParam {Number} per_page Items per page for pagination
     * @apiSuccess {Object} data Leakage line items with pagination
     */
    public function leakageLineItems(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $popupType = $request->popup_type;
        $date = $this->handleDates($request->end_date, $request->campus_flag, true);
        $teamName = $request->team_name;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $Cfs = LeakagePopup::getLeakageLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage);
        return $this->successResponse($Cfs, 'success');

    }

    /**
     * Get Leakage Line Items Details Data
     *
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with leakage line items details data
     * 
     * @api {get} /get-leakage-line-item-details Get Leakage Line Items Details Data
     * @apiName LeakageLineItemsDetails
     * @apiGroup LeakagePopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} popup_type Type of popup for filtering
     * @apiParam {String} invoice_din Invoice DIN for filtering
     * @apiParam {String} prod_description Product description for filtering
     * @apiParam {String} min Minimum value for filtering
     * @apiSuccess {Object} data Detailed leakage line items information
     */
    public function leakageLineItemsDetails(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $popupType = $request->popup_type;
        $date = $this->handleDates($request->end_date, $request->campus_flag, true);
        $teamName = $request->team_name;
        $invoiceDin = $request->invoice_din;
        $prodDescription = $request->prod_description;
        $min = $request->min;
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $Cfs = LeakagePopup::getAccountLeakageLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $min, $invoiceDin, $prodDescription);
        return $this->successResponse($Cfs, 'success');

    }
}
