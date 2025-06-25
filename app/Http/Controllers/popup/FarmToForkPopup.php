<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use App\Services\FarmToForkService;
use Illuminate\Http\Request;
use App\Models\Popup\FarmToForkModel;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Farm to Fork Popup Controller
 * 
 * @package App\Http\Controllers\Popup
 * @version 1.0
 */
class FarmToForkPopup extends Controller
{
    use DateHandlerTrait;

    protected $service;

    public function __construct(FarmToForkService $service)
    {
        $this->service = $service;
    }

    /**
     * Get Farm to Fork Popup Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with farm to fork popup data
     * 
     * @api {get} /farm-to-fork-popup Get Farm to Fork Popup Data
     * @apiName Index
     * @apiGroup FarmToForkPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Farm to fork popup data with supply chain information
     */
    public function index(Request $request)
    {
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $teamName = $request->team_name;
        $record = json_decode(Redis::get($type.'_f2f_'.$date[0]), true);
        if(empty($record)){
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $firstItem = FarmToForkModel::getFarmToForkPop($costCenter, config('constants.F2F_EXP_ARRAY_ONE'), $date, $campusFlag, $type, $teamName);
            
            $secondItem = FarmToForkModel::getFarmToForkPop($costCenter, config('constants.F2F_EXP_ARRAY_TWO'), $date, $campusFlag, $type, $teamName);
            $f2f = [];
            if (!empty($firstItem) && !empty($secondItem)) {
                $firstItem = collect($firstItem)->keyBy('processing_month_date');
                $secondItem = collect($secondItem)->keyBy('processing_month_date');
                foreach ($firstItem as $key => $firstValue) {
                    if ($secondItem->has($key)) {
                        $firstSpend = $firstValue->amount;
                        $secondSpend = $secondItem[$key]->amount;

                        $spend = $secondSpend > 0 ? round(abs($firstSpend / $secondSpend * 100), 1) : 0;
                       // $f2f[$firstValue->account_id]['name'] = $firstValue->name;
                        $f2f[$firstValue->account_id]['spend'] = $spend;
                    }
                }
            }
            
            if($campusFlag == 7 || $campusFlag == 9 || $campusFlag == 11){
                $fytdPeriods = $date;
            } else {
                $fytdPeriods = $this->getFytdPeriods($request->end_date);
            }
           
            $firstItemYtdArray = FarmToForkModel::getFarmToForkPopYTD($costCenter, config('constants.F2F_EXP_ARRAY_ONE'), $date, $campusFlag,  $fytdPeriods, $type, $teamName);
            
            $secondItemYtdArray= FarmToForkModel::getFarmToForkPopYTD($costCenter, config('constants.F2F_EXP_ARRAY_TWO'), $date, $campusFlag, $fytdPeriods, $type, $teamName);
            $f2fYtd = [];
            if (!empty($firstItemYtdArray) && !empty($secondItemYtdArray)) {
                $firstItemYtd = collect($firstItemYtdArray)->keyBy('processing_month_date');
                $secondItemYtd = collect($secondItemYtdArray)->keyBy('processing_month_date');
                
                foreach ($firstItemYtd as $key => $firstValueYtd) {
                    if ($secondItemYtd->has($key)) {
                        $firstSpend = $firstValueYtd->amount;
                        $secondSpend = $secondItemYtd[$key]->amount;

                        $spend = $secondSpend > 0 ? round(abs($firstSpend / $secondSpend * 100), 1) : 0;
                        // $f2f[$firstValueYtd->account_id]['name'] = $firstValueYtd->name;
                        $f2fYtd[$firstValueYtd->account_id]['spend'] = $spend;
                    }
                }
            }
          
            $final_data = [];
            $first = collect($firstItemYtdArray)->keyBy('account_id');
            foreach($first as $fkey => $first_val) {
                if($fkey == $first_val->account_id){
                    $final_data[] = array(
                        'account_id' => $first_val->account_id,
                        'account_name' => $first_val->name,
                        'period' => $f2f[$fkey],
                        'fytd' => $f2fYtd[$fkey],
                    );
                }
            }
            Redis::set($type.'_f2f_'.$date[0], json_encode($final_data));
            return $this->successResponse($final_data, 'success');
        } else {
            return $this->successResponse($record, 'success');
        }
    }
    public function radisClear(Request $request)
    {
        Redis::flushdb();
        echo 'cache cleared successfully';
    }

    public static function getFytdPeriods(string|array $endDate): array
    {
        if(is_array($endDate)){
            $dates = $endDate;
        } else {
            $dates = explode(',', $endDate);
        }
        $formatted = array_map(fn($d) => Carbon::parse($d)->format('n/d/Y'), $dates);
       
        $fiscalYear = DB::table('dashboard_fiscal_periods')
            ->whereIn('end_date', $formatted)
            ->value('fiscal_year');
        if (! $fiscalYear) {
            return [];
        }
        $maxPeriod = DB::table('dashboard_fiscal_periods')
            ->where('fiscal_year', $fiscalYear)
            ->whereIn('end_date', $formatted)
            ->orderByDesc('fiscal_period')
            ->value('fiscal_period');

        $endDates = DB::table('dashboard_fiscal_periods')
            ->where('fiscal_year', $fiscalYear)
            ->where('fiscal_period', '<=', $maxPeriod)
            ->pluck('end_date')    // these are still in n/d/Y format
            ->map(fn($d) => Carbon::createFromFormat('n/d/Y', $d)->format('Y-m-d'))
            ->toArray();

        return $endDates;
    }

    /**
     * Get Farm to Fork Non-Compliant Popup Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with non-compliant farm to fork data
     * 
     * @api {get} /popup/farm-to-fork/non-compliant Get Farm to Fork Non-Compliant Popup Data
     * @apiName FarmToForkNonCompliantPopup
     * @apiGroup FarmToForkPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {Number} page Page number for pagination (default: 1)
     * @apiParam {Number} per_page Items per page (default: 10)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Non-compliant farm to fork data with pagination
     */
    public function farmToForkNonCompliantPopup(Request $request)
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
        $farmToFork = FarmToForkModel::getNonComplaintFarmToFork($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage);
        return $this->successResponse($farmToFork, 'success');
    }

    /**
     * Get Farm to Fork Line Items Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with farm to fork line items data
     * 
     * @api {get} /popup/farm-to-fork/line-items Get Farm to Fork Line Items Data
     * @apiName FarmToForkLineItems
     * @apiGroup FarmToForkPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} popup_type Type of popup for filtering
     * @apiParam {Number} page Page number for pagination (default: 1)
     * @apiParam {Number} per_page Items per page (default: 10)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Paginated farm to fork line items data
     */
    public function farmToForkLineItems(Request $request){
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
        $farmToFork = FarmToForkModel::getFarmToForkLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage);
        return $this->successResponse($farmToFork, 'success');
    }

    /**
     * Get Farm to Fork Line Items Details Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with farm to fork line items details data
     * 
     * @api {get} /popup/farm-to-fork/line-items-details Get Farm to Fork Line Items Details Data
     * @apiName FarmToForkLineItemsDetails
     * @apiGroup FarmToForkPopup
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} campus_flag Campus flag identifier
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} popup_type Type of popup for filtering
     * @apiParam {String} mfr_item_code Manufacturer item code
     * @apiParam {Number} page Page number for pagination (default: 1)
     * @apiParam {Number} per_page Items per page (default: 10)
     * @apiParam {String} end_date End date for data range
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} data Detailed farm to fork line items data
     */
    public function farmToForkLineItemsDetails(Request $request){
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
        $farmToFork = FarmToForkModel::getAccountFarmToForkLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $mfrItemCode, $page, $perPage);
        return $this->successResponse($farmToFork, 'success');
    }
}
