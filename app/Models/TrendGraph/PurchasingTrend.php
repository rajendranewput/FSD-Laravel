<?php

namespace App\Models\TrendGraph;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class PurchasingTrend extends Model
{
    use HasFactory;

    static function getPurchasingTrend($year, $costCenter, $category){
        $corData = DB::table('trend_purchasing_' . $year)
        ->selectRaw('
            SUM(first_spend) as first_spend, 
            SUM(second_spend) as second_spend, 
            CONCAT(pd, "-", category) AS pc, 
            category, 
            cost_center, 
            date, 
            nickname, 
            pd
        ')
        ->whereIn('cost_center', $costCenter)
        ->whereIn('category', $category)
        ->groupBy('category', 'pd', 'cost_center', 'date', 'nickname')
        ->orderBy('pd', 'ASC')
        ->get();

        
        return $corData;
    }

    static function getFormToForkTrend($year, $costCenters){
        $firstItems = DB::table('gl_codes as gc')
            ->select([
                'dfp.nickname', 
                'dfp.fiscal_period', 
                'gc.end_date',
                DB::raw('DATE_FORMAT(gc.end_date, "%y %b") AS date'),
                DB::raw('DATE_FORMAT(STR_TO_DATE(dfp.end_date, "%m/%d/%Y"), "%Y-%m-%d") as p_date'),
                DB::raw('SUM(gc.amount) as amount')
            ])
            ->join('dashboard_fiscal_periods as dfp', function ($join) use ($year) {
                $join->on(DB::raw('STR_TO_DATE(dfp.end_date, "%m/%d/%Y")'), '=', 'gc.end_date')
                    ->whereRaw('dfp.fiscal_year = ?', [$year]); // Using whereRaw() instead of where()
            })
            ->whereIn('gc.unit_id', $costCenters)
            ->whereIn('gc.exp_1', F2F_EXP_ARRAY_ONE)
            ->groupBy('gc.end_date', 'dfp.nickname', 'dfp.fiscal_period', 'dfp.end_date') 
            ->orderBy('gc.end_date', 'ASC')
            ->get()
            ->keyBy('end_date');


        
            $secondItems = DB::table('gl_codes as gc')
                ->select([
                    'dfp.nickname', 
                    'dfp.fiscal_period', 
                    'gc.end_date',
                    DB::raw('DATE_FORMAT(gc.end_date, "%y %b") AS date'),
                    DB::raw('DATE_FORMAT(STR_TO_DATE(dfp.end_date, "%m/%d/%Y"), "%Y-%m-%d") as p_date'),
                    DB::raw('SUM(gc.amount) as amount')
                ])
                ->join('dashboard_fiscal_periods as dfp', function ($join) use ($year) {
                    $join->on(DB::raw('STR_TO_DATE(dfp.end_date, "%m/%d/%Y")'), '=', 'gc.end_date')
                        ->whereRaw('dfp.fiscal_year = ?', [$year]); // Using whereRaw() instead of where()
                })
                ->whereIn('gc.unit_id', $costCenters)
                ->whereIn('gc.exp_1', F2F_EXP_ARRAY_TWO)
                ->groupBy('gc.end_date', 'dfp.nickname', 'dfp.fiscal_period', 'dfp.end_date') 
                ->orderBy('gc.end_date', 'ASC')
                ->get()
                ->keyBy('end_date');
        
        $farmToFork = [];
        
        if (!$firstItems->isEmpty() || !$secondItems->isEmpty()) {
            foreach ($firstItems as $key => $firstValue) {
                if ($secondItems->has($key)) {
                    $secondValue = $secondItems[$key];
                    $firstSpend = $firstValue->amount;
                    $secondSpend = $secondValue->amount;
                    $nickName = strtoupper($secondValue->nickname);
        
                    if (empty($firstSpend)) {
                        $farmToFork[] = [
                            'value' => number_format(0, 1),
                            'date' => $secondValue->date,
                            'nickname' => $nickName,
                            'pd' => $firstValue->p_date
                        ];
                    } else {
                        if (!empty($secondSpend)) {
                            $farmToFork[] = [
                                'value' => number_format(abs($firstSpend / $secondSpend) * 100, 1),
                                'date' => $firstValue->date,
                                'nickname' => $nickName,
                                'pd' => $firstValue->p_date
                            ];
                        }
                    }
                }
            }
        }
        
        return $farmToFork;
        
    }
    public static function f2fFytdDates($year)
    {
        // Fetch data using the query builder
        $data = DB::table('dashboard_fiscal_periods as df')
            ->select([
                'df.end_date',
                'df.nickname',
                DB::raw('DATE_FORMAT(STR_TO_DATE(df.end_date, "%m/%d/%Y"), "%Y-%m-%d") as p_date')
            ])
            ->join('dashboard_aggregates_meta as dm', 'dm.end_date', '=', 'df.end_date')
            ->where('df.fiscal_year', $year)
            ->where('dm.status', 'CORDATA')
            ->get();
        
        // Transform the results into the desired format
        $f2f_fytd = $data->map(function ($value) {
            return [
                'end_date' => date('Y-m-d', strtotime($value->end_date)),
                'nick_name' => $value->nickname
            ];
        });

        return $f2f_fytd->toArray();
    }

    public static function getFormToForkTrendYTD($year, $costCenter){
        $fytdData = self::f2fFytdDates($year);
        $farmToFork = [];
    
        // Loop through fiscal year-to-date data
        foreach ($fytdData as $fytd) {
            // Get the date for filtering
            $date = $fytd['end_date'];
    
            // Query for first set of data (EXP_ARRAY_ONE)
            $firstItems = DB::table('gl_codes')
                ->whereIn('exp_1', F2F_EXP_ARRAY_ONE)
                ->whereIn('unit_id', $costCenter)
                ->whereIn('end_date', [$date])
                ->sum('amount'); // Directly get the sum of 'amount'
    
            // Query for second set of data (EXP_ARRAY_TWO)
            $secondItems = DB::table('gl_codes')
                ->whereIn('exp_1', F2F_EXP_ARRAY_TWO)
                ->whereIn('unit_id', $costCenter)
                ->whereIn('end_date', [$date])
                ->sum('amount'); // Directly get the sum of 'amount'
    
            // If values exist, calculate the spend
            if ($firstItems && $secondItems) {
                $spend = number_format(ABS($firstItems / $secondItems) * 100, 1);
            } else {
                $spend = 0; // Set to 0 if there's no valid amount for calculation
            }
    
            // Build the sub_category data
            $subCategory = [
                'value' => $spend,
                'date' => date('y M', strtotime($fytd['end_date'])),
                'nickname' => $fytd['nick_name'],
                'pd' => $fytd['end_date']
            ];
    
            // Append to final array
            $farmToFork[] = $subCategory;
        }
    
        return $farmToFork;
    }

    static function getLeakageTrend($year, $costCenter){
        $results = DB::table('leakages as l')
            ->select([
                'dfp.nickname',
                DB::raw('DATE_FORMAT(l.end_date, "%y %b") AS date'),
                DB::raw('DATE_FORMAT(STR_TO_DATE(dfp.end_date, "%m/%d/%Y"), "%Y-%m-%d") AS pd'),
                DB::raw('ROUND(IFNULL(SUM(l.leakage_total_spend), 0), 2) AS value')
            ])
            ->join('dashboard_fiscal_periods as dfp', function ($join) use ($year) {
                $join->on(DB::raw('DATE_FORMAT(STR_TO_DATE(dfp.end_date, "%m/%d/%Y"), "%Y-%m-%d")'), '=', 'l.end_date')
                    ->where('dfp.fiscal_year', $year);
            })
            ->whereIn('l.unit', $costCenter) // Use the correct variable $costCenter
            ->where('l.is_deleted', 0)
            ->groupBy('l.end_date', 'dfp.end_date', 'dfp.nickname', 'dfp.fiscal_period') // Added 'dfp.nickname' and 'dfp.fiscal_period' to ensure proper grouping
            ->orderBy('l.end_date', 'ASC')
            ->get();
        return $results;
    }
    static function getFiscalPeriodsData($year){
        $results = DB::table('dashboard_fiscal_periods')
            ->select([
                'start_date', 
                'end_date', 
                'fiscal_period', 
                'nickname', 
                DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-%d") AS pd')
            ])
            ->where('fiscal_year', $year)
            ->orderBy('fiscal_period', 'ASC')
            ->get();
    
        return $results;
    
    }

    static function getPurchasingDate(){
        $result = DB::table('purchases_meta')
            ->selectRaw('MAX(processing_month_date) as processing_month_date')
            ->groupBy('processing_month_date')
            ->get();

        return $result;

    }

}
