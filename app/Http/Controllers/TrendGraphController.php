<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\TrendGraph\PurchasingTrend;
use DateTime;
use Carbon\Carbon;

/**
 * Trend Graph Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class TrendGraphController extends Controller
{
    //
    use PurchasingTrait;

    /**
     * Get Purchasing Trend Graph Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with trend graph data
     * 
     * @api {get} /trend-purchasing Get Purchasing Trend Graph
     * @apiName PurchasingTrendGraph
     * @apiGroup TrendGraph
     * @apiParam {Number} year Fiscal year for data retrieval
     * @apiParam {String} type Data type (campus or other)
     * @apiParam {String} team_name Team identifier
     * @apiSuccess {Object} current_year Current year trend data
     * @apiSuccess {Object} previous_year Previous year trend data
     */
    public function purcahasingTrendGraph(Request $request){
        $year = $request->year;
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        $data = $this->trendOverData($year, $costCenter);
        $current_trend = $this->processTrendOverPastData($data, $year);
        $pre_trend = null;
        if($year > 2022){
            $previous_year = $year -1;
            if(in_array('99999', $costCenter) || in_array('99997', $costCenter) || in_array('99998', $costCenter)){
                $costCenter = array(32659);
            } else {
                $costCenter = $costCenter;
            }
            $pre_data = $this->trendOverData($previous_year, $costCenter);
            $pre_trend = $this->processTrendOverPastData($pre_data, $previous_year);
        }
        $data_array = array(
            'current_year' => $current_trend,
            'previous_year' => $pre_trend
        );
        return $this->successResponse($data_array, 'success');
    }

    /**
     * Process Trend Data Over Time
     * 
     * @param int $year The fiscal year for data processing
     * @param array $costCenter Array of cost center identifiers
     * @return array Processed trend data organized by category
     */
    function trendOverData($year, $costCenter){
        $corCategories = ['ground_beef', 'chicken', 'turkey', 'pork', 'eggs', 'milk_yogurt', 'fish_seafood', 'cooked', 'pdv_total', 'seafood', 'meat'];
        $corData = PurchasingTrend::getPurchasingTrend($year, $costCenter, $corCategories);
        $firstItem = $corData->keyBy('pc');
        $finalData = [];
        $allData = [];
        
        foreach ($firstItem as $key => $firstValue) {
            $firstSpend = $firstValue->first_spend;
            $secondSpend = $firstValue->second_spend;
            $nickName = trim(strtoupper($firstValue->nickname));

            $subCategory = [
                'value' => 0,
                'date' => $firstValue->date,
                'nickname' => $nickName,
                'pd' => $firstValue->pd,
            ];

            $categoryCodeKey = $firstValue->category;

            // Handle specific category logic
            if ($categoryCodeKey === 'cooked') {
                $subCategory['value'] = round($firstSpend, 2);
            } elseif (in_array($categoryCodeKey, ['pdv_total', 'seafood', 'meat'])) {
                if (empty($firstSpend)) {
                    $subCategory['value'] = round(0, 1);
                } else {
                    $subCategory['value'] = ($secondSpend > 0) ? round(abs($firstSpend / $secondSpend) * 100, 0) : "NA";
                }
            } else {
                // Process general category
                if (isset($allData[$firstValue->pd])) {
                    $allData[$firstValue->pd]['first_spend'] += $firstSpend;
                    $allData[$firstValue->pd]['second_spend'] += $secondSpend;
                } else {
                    $allData[$firstValue->pd] = [
                        'first_spend' => $firstSpend,
                        'second_spend' => $secondSpend,
                        'date' => $firstValue->date,
                        'nickname' => $nickName,
                        'pd' => $firstValue->pd,
                    ];
                }

                if (!empty($firstSpend) && !empty($secondSpend)) {
                    if (empty($firstSpend)) {
                        $subCategory['value'] = round(0, 1);
                    } else {
                        $subCategory['value'] = ($secondSpend > 0) ? round(abs($firstSpend / $secondSpend) * 100, 0) : "NA";
                    }
                }
            }

            $finalData[$categoryCodeKey][] = $subCategory;
        }

        // Sorting data by `pd`
        ksort($allData);

        // Process `total` category
        foreach ($allData as $key => $value) {
            $totalSpend = ($value['second_spend'] > 0) ? round(($value['first_spend'] / $value['second_spend']) * 100) : 0;
            $finalData['total'][] = [
                'value' => $totalSpend,
                'date' => $value['date'],
                'nickname' => $value['nickname'],
                'pd' => $value['pd'],
            ];
        }
        $formToFork = PurchasingTrend::getFormToForkTrend($year, $costCenter);
        $farmToForkYear = PurchasingTrend::getFormToForkTrendYTD($year, $costCenter);
        if(!empty($formToFork)) {
            $finalData['period'] = $formToFork;
            $finalData['year_to_date'] = $farmToForkYear;
        }
        $leakageFromVendor = PurchasingTrend::getLeakageTrend($year, $costCenter);
        if(!empty($leakageFromVendor)) {
            $finalData['leakage'] = $leakageFromVendor;
        }
        return $finalData;
    }
    
    /**
     * Process Trend Data for Past Periods
     * 
     * @param array $data Raw trend data
     * @param int $year Fiscal year
     * @return array Processed trend data with complete period coverage
     */
    function processTrendOverPastData($data, $year){
        $categories = $this->getCategory();
        $categories = array_column($categories,'subcategories','key');
        $fiscalPeriod = PurchasingTrend::getFiscalPeriodsData($year);
        $purchasingPeriod = PurchasingTrend::getPurchasingDate();
        $categories = collect($categories)->map(function ($sub_categories, $c_key) use ($data, $fiscalPeriod, $purchasingPeriod) {
            $final_data = [];
        
            if (!empty($sub_categories)) {
                // Reindex the sub_categories array by 'key'
                $sub_categories = collect($sub_categories)->keyBy('key');
        
                foreach ($sub_categories as $s_key => $sub_category) {
                    // Remove unnecessary fields
                   // $sub_category = $sub_category->forget(['name', 'key', 'is_dummy']);
        
                    if (isset($data[$s_key])) {
                        // Check if the key is one of the specific categories
                        if (in_array($s_key, ['total', 'ground_beef', 'chicken', 'turkey', 'pork', 'eggs', 'milk_yogurt', 'fish_seafood', 'periods', 'year_to_date', 'cooked', 'leakage', 'pdv_total', 'seafood', 'meat'])) {
                            $data[$s_key] = $this->assign_empty_data($data[$s_key], $fiscalPeriod, $purchasingPeriod);
                        }
        
                        // Add the data for that key
                        $final_data[$s_key] = $data[$s_key];
                    } else {
                        // If no data exists for the key, return the default sub_category
                        $final_data[$s_key] = $sub_category;
                    }
                }
            }
        
            // Return the updated categories with final data
            return $final_data;
        })->toArray();
        
        return $categories;
        
    }

    /**
     * Assign Empty Data for Missing Periods
     * 
     * @param array $data Existing trend data
     * @param array $fiscal_period Fiscal period data
     * @param array $purchasing_period Purchasing period data
     * @return array Complete trend data with all periods represented
     */
    function assign_empty_data($data, $fiscal_period, $purchasing_period)
    {
        // Convert to collections and key by 'pd' and 'processing_month_date'
        $data = collect($data)->keyBy('pd');
        $purchasing_period = collect($purchasing_period)->keyBy('processing_month_date');
        $fiscal_period = collect($fiscal_period)->keyBy('pd');
    
        // Loop through fiscal period and assign default data if necessary
        foreach ($fiscal_period as $key => $value) {
            // If data for the given 'pd' doesn't exist, add default values
            if (!$data->has(trim($key))) {
                $data->put(trim($key), [
                    'value' => "NA",
                    'date' => Carbon::parse($value->pd)->format('y M'),
                    'nickname' => strtoupper($value->nickname),
                    'pd' => $value->pd,
                ]);
            }
        }
    
        // Sort the data by 'pd'
        $data = $data->sortKeys()->values();
    
        return $data->toArray();
    }
    
}
