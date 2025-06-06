<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\widgetRequest;
use App\Traits\DateHandlerTrait;
use App\Models\FiscalPeriod;
use Illuminate\Support\Facades\Redis;


class FiscalPeriodController extends Controller
{
    //
    use DateHandlerTrait;
    public function getFiscalYear(){
        try{
            $data = FiscalPeriod::getyears();
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
    public function getFiscalPeriod(Request $request){
        try{
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $data = FiscalPeriod::getPeriod($costCenter, $request->year, $request->latest, $request->campus_flag);
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
    public function checkForPopups(widgetRequest $request){
        $validated = $request->validated();

        //try{
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
            return response()->json([
                'status' => 'success',
                'data' => $dataArray,
            ], 200);
        // } catch(\Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], 500);
        // }

    }

    public function getLatestPeriod(){
        $data = FiscalPeriod::getLatestPeriod();
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }
}
