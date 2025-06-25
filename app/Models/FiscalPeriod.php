<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FiscalPeriod extends Model
{
    use HasFactory;

    static function getyears(){
        $data = DB::table('dashboard_fiscal_periods')
        ->select('fiscal_year')
        ->where('fiscal_year', '!=', '2015')
        ->groupBy('fiscal_year')
        ->get();
        return $data;
    }
    static function getPeriod($costCenter, $year, $latest, $campusFlag){
        $todayDate = date('Y-m-01');
        $costCenters = is_array($costCenter) ? $costCenter : [$costCenter];
        if ($campusFlag == '0' || $campusFlag == '1' || $campusFlag == '2' || $campusFlag == '3') {
            // Get all required processing months
            $periods = DB::table('dashboard_fiscal_periods')
                ->select(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01") as end_date'))
                ->where('fiscal_year', $year)
                ->where(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01")'), '<=', $todayDate)
                ->pluck('end_date')
                ->toArray();

            $existingRecords = DB::table('customer_counts')
                ->select('unit_number', 'processing_month')
                ->whereIn('processing_month', $periods)
                ->whereIn('unit_number', $costCenters)
                ->get()
                ->map(function ($item) {
                    return $item->unit_number . '_' . $item->processing_month;
                })
                ->toArray();
                
            // Generate all possible combinations of unit_number and period
            $missingRecords = [];
            foreach ($costCenters as $unit) {
                foreach ($periods as $period) {
                    $key = $unit . '_' . $period;
                    if (!in_array($key, $existingRecords)) {
                        $missingRecords[] = [
                            'unit_number' => $unit,
                            'processing_month' => $period
                        ];
                    }
                }
            }

            // Extract unique missing periods
            $missingPeriods = array_unique(array_column($missingRecords, 'processing_month'));
           
            // Get Fiscal Period Details
            $fiscalPeriods = DB::table('dashboard_fiscal_periods')
                ->select(
                    DB::raw('DATE_FORMAT(STR_TO_DATE(start_date, "%m/%d/%Y"), "%m/%d/%Y") as start_date'),
                    DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01") as end_date'),
                    DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%m/%d/%Y") as period_end_date'),
                    'nickname',
                    'fiscal_period',
                    'fiscal_year'
                )
                ->whereIn(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01")'), $periods)
                ->get()
                ->map(function ($item) use ($missingPeriods) {
                    // Highlight missing periods in red
                    $item->color = in_array($item->end_date, $missingPeriods) ? 'red' : 'black';
                    return $item;
                });

            return $fiscalPeriods;
            } else {
                $data = DB::table('dashboard_fiscal_periods as dp')
                ->select('fiscal_period', 'end_date as period_end_date', 'start_date', 'nickname', 'fiscal_year')
                ->where('fiscal_year', $year)
                ->where(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01")'), '<=', $todayDate)
                ->get()
                ->map(function ($item) {
                    $item->color = 'black'; // Add a new key-value pair
                    return $item;
                });
                return $data;
            }
        
    }
    static function getCheckBackSoon($cost_center, $year, $date){
        $data = DB::table('dashboard_aggregates_meta')
            ->select('end_date')
            ->where('status', 'CORDATA')
            ->where(function ($query) use ($date) {
                foreach ($date as $d) {
                    $query->orWhereRaw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01") = ?', [$d]);
                }
            })
            ->first();
        return $data;
    }

    static function getMissingCustomer($costCenter, $year, $date, $campusFlag){
        $costCenters = is_array($costCenter) ? $costCenter : [$costCenter];
        if($campusFlag == '2' || $campusFlag == '3'){
            // Get all required processing months
            $periods = DB::table('dashboard_fiscal_periods')
                ->select(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01") as end_date'))
                ->where('fiscal_year', $year)
                ->pluck('end_date')
                ->toArray();
        } else {
            $periods = is_array($date) ? $date : [$date];
        }
            // Get existing (unit_number, processing_month) pairs
            $existingRecords = DB::table('customer_counts')
                ->select('unit_number', 'processing_month')
                ->whereIn('processing_month', $periods)
                ->whereIn('unit_number', $costCenters)
                ->get()
                ->map(function ($item) {
                    return $item->unit_number . '_' . $item->processing_month;
                })
                ->toArray();

            // Generate all possible combinations of unit_number and period
            $missingRecords = [];
            foreach ($costCenters as $unit) {
                foreach ($periods as $period) {
                    $key = $unit . '_' . $period;
                    if (!in_array($key, $existingRecords)) {
                        $missingRecords[] = [
                            'unit_number' => $unit, 
                            'processing_month' => $period
                        ];
                    }
                }
            }

            // Extract unique unit numbers and periods
            $cost = array_unique(array_column($missingRecords, 'unit_number'));
            $end_date = array_unique(array_column($missingRecords, 'processing_month'));

            // Get Café details
            $cafeDetails = DB::table('cafes as c')
                ->select('c.name', 'c.cost_center')
                ->whereIn('cost_center', $cost)
                ->groupBy('c.cost_center', 'c.name')
                ->get()
                ->keyBy('cost_center'); // Store as key-value for easier mapping

            // Get Fiscal Period Details
            $fiscalPeriods = DB::table('dashboard_fiscal_periods')
                ->select(
                    DB::raw('DATE_FORMAT(STR_TO_DATE(start_date, "%m/%d/%Y"), "%Y-%m-%d") as start_date'),
                    DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01") as end_date'),
                    DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-%d") as period_end_date'),
                    'nickname',
                    'fiscal_period',
                    'fiscal_year'
                )
                ->whereIn(DB::raw('DATE_FORMAT(STR_TO_DATE(end_date, "%m/%d/%Y"), "%Y-%m-01")'), $end_date)
                ->get()
                ->keyBy('end_date'); // Store as key-value for easier mapping

            // Map the missing records with café and fiscal period details
            $finalData = array_map(function ($record) use ($cafeDetails, $fiscalPeriods) {
                return [
                    'unit_number' => $record['unit_number'],
                    'processing_month' => $record['processing_month'],
                    'cafe_name' => $cafeDetails[$record['unit_number']]->name ?? 'N/A',
                    'start_date' => $fiscalPeriods[$record['processing_month']]->start_date ?? 'N/A',
                    'end_date' => $fiscalPeriods[$record['processing_month']]->end_date ?? 'N/A',
                    'period_end_date' => $fiscalPeriods[$record['processing_month']]->period_end_date ?? 'N/A',
                    'fiscal_period' => $fiscalPeriods[$record['processing_month']]->fiscal_period ?? 'N/A',
                    'fiscal_year' => $fiscalPeriods[$record['processing_month']]->fiscal_year ?? 'N/A',
                ];
            }, $missingRecords);

            // Step 8: Convert to collection (optional)
            $finalCollection = collect($finalData);
            return $finalCollection;
    }

    static function getLatestPeriod(){
        $result = DB::table('dashboard_aggregates_meta as d')
        ->select('d.end_date', 'd.start_date', 'dp.nickname', 'dp.fiscal_period', 'dp.fiscal_year')
        ->join('dashboard_fiscal_periods as dp', 'dp.end_date', '=', 'd.end_date')
        ->where('d.status', 'CORDATA')
        ->orderBy('id', 'desc')
        ->first();
        return $result;
    }
}
