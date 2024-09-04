<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests\widgetRequest;
use App\Traits\DateHandlerTrait;
use App\Models\Purchasing;
use DateTime;

class PurchasingController extends Controller
{
    /*** To get farm to form data */
    public function farmForkSpendData(Request $request)
    {
        $checkCampusRollSummary = app('check.campusRollSummary')($request);
        $checkAccCampusRoll = app('check.accCampusRoll')($request);
        $checkCampusRoll = app('check.campusRoll')($request);
        $checkDate = app('check.date')($request);
        if($checkCampusRollSummary){
            $endDate = $checkDate;
            $this->backwardDate = date('Y', strtotime(config('constants.BACKWARD_COMPATIBILITY_CHECK_DATE')));
        } else {
            if(is_array($checkDate)){
                foreach($checkDate as $date){
                    $pDate = date_create_from_format("m-d-Y", $date)->format("Y-m-d");
                    $pDate = new \DateTime($pDate);
                    $endDate[] = $pDate->format('Y-m-d');
                    $fytdPeriods = Purchasing::fytdPeriods($endDate);
                    $backDate = 0;
                }
            } else {
                $pDate = date_create_from_format("m-d-Y", $checkDate)->format("Y-m-d");
                $pDate = new \DateTime($pDate);
                $endDate[] = $pDate->format('Y-m-d');
                $fytdPeriods = Purchasing::fytdPeriods($endDate);
                $backDate = 0;
            }
        }
        
        $backwardDate = false;
        $selectPeriodString = 'SELECT SUM(f2f_period) as f2f_period, SUM(total_spend) as total_spend'; //Preparing select query for period
        $selectYearString = 'SELECT SUM(f2f_ytd) as f2f_ytd, SUM(ytd_total_spend) as ytd_total_spend'; //Preparing select query for year
        $costCenters = Purchasing::accountDynamicCostCenters($request);
        if($checkCampusRollSummary){
            if($checkCampusRoll){
                $costCenters = $request->costCenters;
            } else if($checkAccCampusRoll){
            } else {
                $costCenters = $this->purchasing_model->dynamicCostCenter($request->dynamicLocation);
            }
            // $farm_to_fork_period_data = $this->farm_to_fork_data($costCenters, $end_date, $campus_roll_up, false, true, 'period', $select_period_string, $backward_date);
            // $farm_to_fork_year_data = $this->farm_to_fork_data($cost_centers, $end_date, $campus_roll_up, false, true, 'year', $select_year_string, $backward_date, $fytd_periods);
        } else{
            
            $farmToForkPeriodData = $this->farmToForkData($request, $request->costCenters, $endDate, $request->campusRollUp, false, true, 'period', $selectPeriodString, $backwardDate);
            $farmToForkYearData = $this->farmToForkData($request, $request->costCenters, $endDate, $request->campusRollUp, false, true, 'year', $selectYearString, $backwardDate,$fytdPeriods);
        }

        $data = array(
            'farmToForkPeriodData' => $farmToForkPeriodData,
            'farmToForkYearData' => $farmToForkYearData
        );
        echo json_encode(array(
            'data' => $data
        ));
    }

    public function farmToForkData($request, $costCenters, $date, $campusRollUp, $lineItem, $trendGraph, $type, $selectString,$backwardDate, $fytdPeriods = array()){
        $costCenters = app('join.costCenters')($costCenters, $campusRollUp);
        $farmToFork = Purchasing::farmToForkData($request, $costCenters, $date, $campusRollUp, $type, $fytdPeriods);
        $colorThreshold = app('color.threshold')($farmToFork);
        return app('ff.response')($farmToFork, $colorThreshold, $lineItem, $trendGraph);
    }

    /*** get cooked and leakage data*/
    public function purchasingCookedLeakageData(Request $request){
        $checkDate = app('check.date')($request);
        $checkCampusRollSummary = app('check.campusRollSummary')($request);
        $checkAccCampusRoll = app('check.accCampusRoll')($request);
        $checkCampusRoll = app('check.campusRoll')($request);
        $checkallLevelFlag = app('check.allLevelFlag')($request);
        if($checkCampusRollSummary){
            $endDate = $checkDate;
            $this->backwardDate = date('Y', strtotime(config('constants.BACKWARD_COMPATIBILITY_CHECK_DATE')));
        } else {
            if(is_array($checkDate)){
                foreach($checkDate as $date){
                    $pDate = DateTime::createFromFormat('Y-m-d', $date);
                    $pDate = new \DateTime($pDate);
                    $endDate[] = $pDate->format('Y-m-d');
                    $back_date = 0;
                }
            } else {
                $pDate = DateTime::createFromFormat('Y-m-d', $checkDate);
                $pDate = new \DateTime($pDate);
                $endDate[] = $pDate->format('Y-m-d');
                $back_date = 0;
            }
            $this->backward_date = '';
        }
        
        if($checkallLevelFlag){
            if($checkCampusRoll){
                $costCenters = $request->costCenters;
            } else if($checkAccCampusRoll){
                $costCenters = '';//$this->purchasing_model->account_dynamic_cost_center($dynamic_location);
            } else {
                $costCenters = '';//$this->purchasing_model->dynamic_cost_center($dynamic_location);
            }
        } else {
            $costCenters = $request->costCenters;
        }
        $cookedFromScratch = $this->cookedFromScratch($request, $endDate, $costCenters);
        $leakageFromVendors = $this->leakageFromVendors($request, $endDate, $costCenters);
        
        $data = array(
            'cookedFromScratch' => $cookedFromScratch,
            'leakageFromVendors' => $leakageFromVendors
        );
        echo json_encode(array(
            'data' => $data
        ));
    }

    /*** To get cooked from scratch data */
    public function cookedFromScratch($request, $endDate, $costCenters){
        foreach ($endDate as $dates ) {
            $backDate = 0;
        }
       
        $costCenter = app('join.costCenters')($costCenters, $request->campusRollUp);
        $cookedData = Purchasing::cookedFromScratch($request, $costCenter, $endDate, $request->campusRollUp);
        //$color_threshold = get_pps_threshold($cookedData);
        return array(
            'amount' => $cookedData,
            'color_threshold' => '',//$color_threshold,
            'line_item' => true,
            'trend_graph' => true,
        );
    }

    /*** To get leakage from vendors data */
    public function leakageFromVendors($request, $endDate, $costCenters){
        foreach ($endDate as $dates ) {
            $backDate = 0;
        }
        
        $costCenter = app('join.costCenters')($costCenters, $request->campusRollUp);
        $leakageData = Purchasing::leakageFromVendors($request, $costCenter, $endDate, $request->campusRollUp);
       // $color_threshold = get_pps_threshold($leakageData);
        return array(
            'amount' => $leakageData,
            'color_threshold' => '',//$color_threshold,
            'line_item' => true,
            'trend_graph' => true,
        );
    }
    
    /*** To get farm to fork GL code data */
    public function farmToForkGLCodeData(Request $request){
        //$validated = $request->validated();
        try{
            $endDate = $this->handleDates($request->end_date, $request->campus_flag);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $graphData = Purchasing::farmToForkGLCodeData($request, $costCenter, $endDate, $request->campus_flag);
            $farmToFormGLData = array(
                'graph' => $graphData,
                'line_item' => true,
                'trend_graph' => true,
            );
            echo json_encode(array(
                'data' => $farmToFormGLData
            ));
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
