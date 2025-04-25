<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use App\Services\FarmToForkService;
use Illuminate\Http\Request;
use App\Models\Popup\FarmToForkModel;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;

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
                        $f2f[$firstValue->account_id]['name'] = $firstValue->name;
                        $f2f[$firstValue->account_id]['spend'] = $spend;
                    }
                }
            }

            Redis::set($type.'_f2f_'.$date[0], json_encode($f2f));
            return response()->json([
                'status' => 'success',
                'data' => $f2f,
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'data' => $record,
            ], 200);
        }
    }
}
