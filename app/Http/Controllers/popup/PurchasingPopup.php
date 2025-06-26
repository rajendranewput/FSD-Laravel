<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Popup\PurchasesPopOut;
use App\Traits\PurchasingTrait;

/**
 * Purchasing Popup Controller
 * 
 * @package App\Http\Controllers\Popup
 * @version 1.0
 */
class PurchasingPopup extends Controller
{
    //
    use DateHandlerTrait, PurchasingTrait;

    /**
     * Get Purchasing Popup Data
     *
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with purchasing popup data
     * 
     * @api {get} /cor-total-popup Get Purchasing Popup Data
     * @apiName GetPopup
     * @apiGroup PurchasingPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiSuccess {Object} data Purchasing popup data with COR information
     */
    public function getPopup(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $record = json_decode(Redis::get($type.'_'.$date[0]), true);
        if(empty($record)){
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $corCategories = ['ground_beef', 'chicken', 'turkey', 'pork', 'eggs', 'milk_yogurt', 'fish_seafood'];
            $corTotal = PurchasesPopOut::getTotalCor($date, $year, $campusFlag, $type, $costCenter, $corCategories);
            $corTotal = collect($corTotal)->keyBy('account_id')->toArray();
            $cor = PurchasesPopOut::getCor($date, $year, $campusFlag, $type, $costCenter, $corCategories);
            $corList = collect($cor)->keyBy('account_id')->toArray();
            foreach($corList as $data){
                $corList[$data['account_id']]['cor_data']['total'] = $corTotal[$data['account_id']];
            }
            Redis::set($type.'_'.$date[0], json_encode($corList));
            return $this->successResponse($corList, 'success');
        } else {
            return $this->successResponse($record, 'Purchasing popup data retrieved from cache');
        }
    }

    /**
     * Get Line Item Data
     *
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with line item data
     * 
     * @api {get} /get-cor-line-item-popup Get Line Item Data
     * @apiName GetLineItem
     * @apiGroup PurchasingPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} category Category for filtering line items
     * @apiParam {Number} page Page number for pagination
     * @apiParam {Number} per_page Items per page for pagination
     * @apiSuccess {Object} data Line item data with pagination
     */
    public function getLineItem(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $category = $request->category;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $categoryCode = $this->getCategoryCode($category);
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        if(!empty($category)){
            $lineItemData = PurchasesPopOut::getLineItem($date, $year, $campusFlag, $type, $costCenter, $categoryCode, $page, $perPage);
        } else {
            $lineItemData = PurchasesPopOut::getTotalLineItem($date, $year, $campusFlag, $type, $costCenter, $page, $perPage);
        }
        return $this->successResponse($lineItemData, 'success');
    }

    /**
     * Get Account COR Item Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with account COR item data
     * 
     * @api {get} /get-account-cor-item Get Account COR Item Data
     * @apiName GetAccountCORItem
     * @apiGroup PurchasingPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} category Category for filtering
     * @apiParam {String} mfr_item_code Manufacturer item code for specific item
     * @apiParam {Number} page Page number for pagination
     * @apiParam {Number} per_page Items per page for pagination
     * @apiSuccess {Object} data Account-specific COR item data
     */
    public function getAccountCORItem(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $category = $request->category;
        $mfrItemCode = $request->mfr_item_code;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
            $lineItemData = PurchasesPopOut::getAccountItem($date, $year, $campusFlag, $type, $costCenter, $mfrItemCode, $category, $page, $perPage);
      
        return $this->successResponse($lineItemData, 'success');
    }

}
