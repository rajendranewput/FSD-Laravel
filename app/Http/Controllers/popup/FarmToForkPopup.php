<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use App\Services\FarmToForkService;
use Illuminate\Http\Request;
use App\Models\Popup\FarmToForkModel;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Cache;

class FarmToForkPopup extends Controller
{
    use DateHandlerTrait;

    protected $service;

    public function __construct(FarmToForkService $service)
    {
        $this->service = $service;
    }

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
            return response()->json([
                'status' => 'success',
                'data' => $final_data,
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'data' => $record,
            ], 200);
        }
    }
    public function radisClear(Request $request)
    {
        Redis::flushdb();
        echo 'cache cleared successfully';
    }

    public static function getFytdPeriods(string|array $endDate): array
    {
        $dates = is_array($endDate) ? $endDate : [ $endDate ];
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
