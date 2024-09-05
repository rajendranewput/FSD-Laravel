<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\widgetRequest;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use App\Models\Farmtofork;
use App\Models\Purchasing;
use Illuminate\Support\Facades\Redis;

class FarmtoforkController extends Controller
{
    use DateHandlerTrait, PurchasingTrait;

    /* To get farm to fork GL code data */
    public function farmToForkGLCodeData(widgetRequest $request){
        $validated = $request->validated();
        try{
            $year = $request->year;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $data = Farmtofork::getGlCodeData($date, $costCenter, $request->campus_flag, $year);
            $produce = 0;
            $meat = 0;
            $cheese = 0;
            $fluid = 0;
            $seafood = 0;
            $sushi = 0;
            $bakery = 0;
            $artisan = 0;
            $coffee = 0;
            $locally = 0;

            foreach($data as $glData){
                if($glData->exp_1 == PRODUCE_GL_CODE){
                    $produce += $glData->amount;
                }
                if($glData->exp_1 == MEAT_GL_CODE){
                    $meat += $glData->amount;
                }
                if($glData->exp_1 == CHEESE_GL_CODE){
                    $cheese += $glData->amount;
                }
                if($glData->exp_1 == FLUID_DAIRY_GL_CODE){
                    $fluid += $glData->amount;
                }
                if($glData->exp_1 == SEAFOOD_GL_CODE){
                    $seafood += $glData->amount;
                }
                if($glData->exp_1 == SUSHI_GL_CODE){
                    $sushi += $glData->amount;
                }
                if($glData->exp_1 == BAKERY_GL_CODE){
                    $bakery += $glData->amount;
                }
                if($glData->exp_1 == ARTISAN_GL_CODE){
                    $artisan += $glData->amount;
                }if($glData->exp_1 == COFFEE_GL_CODE){
                    $coffee += $glData->amount;
                }
                if($glData->exp_1 == LOCALLY_CRAFTED_GL_CODE){
                    $locally += $glData->amount;
                }
            }

            $finalData = array(
                'produce' => array('key' => 'Produce', 'code' => PRODUCE_GL_CODE, 'amount' => round($produce)),
                'meat' => array('key' => 'Meat/Eggs', 'code' => MEAT_GL_CODE, 'amount' => round($meat)),
                'cheese' => array('key' => 'Cheese', 'code' => CHEESE_GL_CODE, 'amount' => round($cheese)),
                'fluid_dairy' => array('key' => 'Fluid Dairy', 'code' => FLUID_DAIRY_GL_CODE, 'amount' => round($fluid)),
                'sea_food' => array('key' => 'Seafood', 'code' => SEAFOOD_GL_CODE, 'amount' => round($seafood)),
                'sushi' => array('key' => 'Sushi', 'code' => SUSHI_GL_CODE, 'amount' => round($sushi)),
                'bakery' => array('key' => 'Bakery', 'code' => BAKERY_GL_CODE, 'amount' => round($bakery)),
                'artisan_other' => array('key' => 'Artisan Other', 'code' => ARTISAN_GL_CODE, 'amount' => round($artisan)),
                'coffee' => array('key' => 'Coffee/Tea', 'code' => COFFEE_GL_CODE, 'amount' => round($coffee)),
                'locally_crafted' => array('key' => 'Locally Crafted', 'code' => LOCALLY_CRAFTED_GL_CODE, 'amount' => round($locally))
            );
            return response()->json([
                'status' => 'success',
                'data' => $finalData,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function farmForkSpendData(widgetRequest $request){
        $validated = $request->validated();
        try{
            $year = $request->year;
            $date = $this->handleDates($request->end_date, $request->campus_flag);
           // $fytdPeriods = Purchasing::fytdPeriods($date);
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            
            $farmToFork = Farmtofork::farmToForkData($date, $costCenter, $request->campus_flag, $year);
            $colorThreshold = $this->getColorThreshold($farmToFork, FARM_FORK_SECTION);
            $percentage = ($farmToFork/FF_FULL_CIRCLE_VALUE)*100;
            $yearPeriodData = array(
                'percentage' => $percentage,
                'display_value' => $farmToFork,
                'color_threshold' => $colorThreshold
            );
            $finalData = array(
                'farmToForkPeriodData' => $yearPeriodData,
                'farmToForkYearData' => $yearPeriodData
            );

            return response()->json([
                'status' => 'success',
                'data' => $finalData,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
