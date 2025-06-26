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

    /**
     * Constructor
     * 
     * @param FarmToForkService $service Farm to fork service instance
     */
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
     * @apiParam {String} team_name Team identifier
     * @apiParam {String} end_date End date for data range
     * @apiSuccess {Object} data Farm to fork popup data with account information
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
          
            $finalData = [];
            $first = collect($firstItemYtdArray)->keyBy('account_id');
            foreach($first as $fkey => $firstVal) {
                if($fkey == $firstVal->account_id){
                    $finalData[] = array(
                        'account_id' => $firstVal->account_id,
                        'account_name' => $firstVal->name,
                        'period' => $f2f[$fkey],
                        'fytd' => $f2fYtd[$fkey],
                    );
                }
            }
            Redis::set($type.'_f2f_'.$date[0], json_encode($finalData));
            return $this->successResponse($finalData, 'success');
        } else {
            return $this->successResponse($record, 'Farm to fork popup data retrieved from cache');
        }
    }

    /**
     * Clear Redis Cache
     * 
     * @param Request $request The incoming HTTP request
     * @return string Success message
     * 
     * @api {get} /radis-clear Clear Redis Cache
     * @apiName RadisClear
     * @apiGroup FarmToForkPopup
     * @apiSuccess {String} message Cache cleared successfully
     */
    public function radisClear(Request $request)
    {
        Redis::flushdb();
        echo 'cache cleared successfully';
    }

    /**
     * Get FYTD Periods
    
     * @param string|array $endDate End date(s) for period calculation
     * @return array Array of fiscal period end dates
     */
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
}
