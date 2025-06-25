<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WidgetRequest;
use App\Traits\DateHandlerTrait;
use App\Models\FiscalPeriod;
use Illuminate\Support\Facades\Redis;

/**
 * Fiscal Period Controller
 * 
 * Manages fiscal period data and date handling. This controller handles
 * the retrieval and processing of fiscal year data, fiscal periods,
 * and popup checking functionality for date-based operations.
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class FiscalPeriodController extends Controller
{
    use DateHandlerTrait;

    /**
     * Get Fiscal Years
     
     * @return JsonResponse JSON response with fiscal years data
     * 
     * @throws \Exception When data retrieval fails
     * 
     * @api {get} /get-fiscal-year Get Fiscal Years
     * @apiName GetFiscalYear
     * @apiGroup FiscalPeriod
     * @apiSuccess {Array} data List of available fiscal years
     */
    public function getFiscalYear(){
        try{
            $data = FiscalPeriod::getyears();
            return $this->successResponse($data, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get Fiscal Period
     * 
     * @throws \Exception When data retrieval fails
     * 
     * @api {get} /get-fiscal-period Get Fiscal Period
     * @apiName GetFiscalPeriod
     * @apiGroup FiscalPeriod
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} latest Latest period flag
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Fiscal period information
     */
    public function getFiscalPeriod(Request $request){
        try{
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $data = FiscalPeriod::getPeriod($costCenter, $request->year, $request->latest, $request->campus_flag);
            return $this->successResponse($data, 'success');
        } catch(\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Check For Popups
    
     * @param WidgetRequest $request The validated HTTP request containing parameters
     * @return JsonResponse JSON response with popup check results
     * 
     * @api {get} /check-for-popups Check For Popups
     * @apiName CheckForPopups
     * @apiGroup FiscalPeriod
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Boolean} check_back_soon Flag indicating if user should check back later
     * @apiSuccess {Array} missing_customer_count Missing customer information
     */
    public function checkForPopups(WidgetRequest $request){
        $validated = $request->validated();

        try{
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $date = $this->handleDates($request->end_date, $request->campus_flag);
           
            $checkBackSoon = FiscalPeriod::getCheckBackSoon($costCenter, $request->year, $date);
            if (!$checkBackSoon) { // If no record is found
                $checkBackSoonPop = true;
            } else {
                $checkBackSoonPop = false;
            }
            
            $missingCustomer = [];

            if($checkBackSoonPop == false){
                if($request->type == 'campus' || $request->type == 'cafe'){
                    $missingCustomer = FiscalPeriod::getMissingCustomer($costCenter, $request->year, $date, $request->campus_flag);
                }
            }
            
            $dataArray = array(
                'check_back_soon' => $checkBackSoonPop,
                'missing_customer_count' => $missingCustomer
            );
            return $this->successResponse($dataArray, 'success');
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Latest Period
     *
     * @return JsonResponse JSON response with latest period data
     * 
     * @api {get} /latest-date Get Latest Period
     * @apiName GetLatestPeriod
     * @apiGroup FiscalPeriod
     * @apiSuccess {Object} data Latest fiscal period information
     */
    public function getLatestPeriod(){
        $data = FiscalPeriod::getLatestPeriod();
        return $this->successResponse($data, 'success');
    }
}
