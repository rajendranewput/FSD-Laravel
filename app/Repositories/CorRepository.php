<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * COR Repository
 * 
 * @package App\Repositories
 * @version 1.0
 */
class CorRepository
{
    /**
     * 
     * @param array $date Array of processing dates
     * @param array $costCenter Array of cost center codes
     * @param string $campusFlag Campus flag identifier
     * @param int $year Fiscal year
     * @return \Illuminate\Support\Collection COR data collection
     */
    public function getCorData(array $date, array $costCenter, string $campusFlag, int $year)
    {
        return DB::table('purchases_' . $year)
            ->select(DB::raw('SUM(spend) as spend'), 'cor', 'mfrItem_parent_category_code')
            ->whereIn('financial_code', $costCenter)
            ->whereIn('processing_month_date', $date)
            ->whereIn('mfrItem_parent_category_code', [
                config('constants.BEEF_CODE'),
                config('constants.CHICKEN_CODE'),
                config('constants.TURKEY_CODE'),
                config('constants.PORK_CODE'),
                config('constants.EGGS_CODE'),
                config('constants.DAIRY_PRODUCT_CODE'),
                config('constants.FISH_AND_SEEFOOD_CODE')
            ])
            ->whereIn('cor', ['-1', '1', '2'])
            ->groupBy('cor')
            ->groupBy('mfrItem_parent_category_code')
            ->get();
    }
} 