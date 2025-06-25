<?php

namespace App\Models\Popup;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CfsPopup extends Model
{
    use HasFactory;

    static function getAccountCfs($costCenters, $date, $campusFlag, $type, $teamName)
    {
        // $costCenters = explode(',', $costCenter);
        $dates = is_array($date) ? $date : [$date];
       // $costCenters = explode(',', $cost_center_csv);
        $finalData = [];
    
        // Case: company summary at sector level
        if ($campusFlag === COMPANY_SUMMARY_FLAG && $type === 'sector') {
            $rows = DB::table('pop_out_summary_data')
                ->select(['team_description as name', 'team_name as account_id', 'data as cfs'])
                ->where('section', 'cfs')
                ->where('popup_type', 'rvp')
                ->where('year', $dates[0])
                ->get();
    
            foreach ($rows as $row) {
                $finalData[] = [
                    'account_id'   => $row->account_id,
                    'account_name' => $row->name,
                    'cfs'          => $row->cfs,
                ];
            }
    
            return $finalData;
        }
    
        $formatSpend = fn($val) => is_numeric($val) ? round($val) : null;
    
        if ($type === 'rvp') {
            $query = DB::table('purchases as p')
                ->select([
                    'p.financial_code',
                    'c.sector_name',
                    'c.district_name',
                    'a.team_description as name',
                    'a.team_name as account_id',
                ])
                ->selectRaw('SUM(IF(cfs = 2 AND spend > 0, spend, 0)) as spend')
                ->where('cfs', 2)
                // use conditional whereIn calls, not array for column
                ->when(in_array($campusFlag, [DM_SUMMARY_FLAG, RVP_SUMMARY_FLAG, COMPANY_SUMMARY_FLAG]),
                    fn($q) => $q->whereIn('p.processing_year', $dates),
                    fn($q) => $q->whereIn('p.processing_month_date', $dates)
                )
                ->whereIn('p.financial_code', $costCenters)
                ->join('wn_costcenter as c', 'c.team_name', '=', 'p.financial_code')
                ->join('wn_district as a', 'a.team_name', '=', 'c.district_name')
                ->where('c.sector_name', 'A00000')
                //->when($teamName !== 'A00000' && $teamName !== '', fn($q) => $q->where('c.region_name', $teamName))
                ->groupBy(
                'p.financial_code',
                'c.sector_name',
                'c.district_name',
                'a.team_description', 'a.team_name',
                )
                ->get();
    
            foreach ($query as $row) {
                $finalData[] = [
                    'account_id'   => $row->account_id,
                    'account_name' => $row->name,
                    'cfs'          => $formatSpend($row->spend),
                ];
            }
    
            return $finalData;
        }
    
        if ($type === 'sector') {
            $rows = DB::table('purchases_meta as p')
                ->select(['p.financial_code', 'a.team_description as name', 'a.team_name as account_id'])
                ->selectRaw('SUM(IF(cfs = 2 AND spend > 0, spend, 0)) as spend')
                ->where('cfs', 2)
                ->when($campusFlag === COMPANY_SUMMARY_FLAG,
                    fn($q) => $q->whereIn('p.processing_year', $dates),
                    fn($q) => $q->whereIn('p.processing_month_date', $dates)
                )
                ->whereIn('p.financial_code', $costCenters)
                ->join(DB::raw('(SELECT team_name, region_name FROM wn_costcenter GROUP BY team_name, region_name) as c'), 'c.team_name', '=', 'p.financial_code')
                ->join('wn_region as a', 'a.team_name', '=', 'c.region_name')
                ->groupBy([
                    'a.team_name',
                    'p.financial_code',
                    'a.team_description',
                ])
                ->orderBy('a.team_name')
                ->get();
    
            foreach ($rows as $row) {
                $finalData[] = [
                    'account_id'   => $row->account_id,
                    'account_name' => $row->name,
                    'cfs'          => $formatSpend($row->spend),
                ];
            }
    
            return $finalData;
        }
    
        // Default: cafe-level
        if (!empty($teamName)) {
            $costCenters = DB::table('wn_costcenter as w')
                ->select('w.team_name')
                ->join('cafes as c', 'c.cost_center', '=', 'w.team_name')
                ->where('w.sector_name', 'A00000')
                ->where('c.display_foodstandard', 1)
                ->when($teamName !== 'A00000', fn($q) => 
                    strlen($teamName) > 5
                        ? $q->where('w.region_name', $teamName)
                        : $q->where('w.district_name', $teamName)
                )
                ->groupBy('w.team_name')
                ->pluck('w.team_name')
                ->toArray();
        }
    
        $rows = DB::table('purchases as p')
            ->select(['p.financial_code', 'a.account_id', 'a.name'])
            ->selectRaw('SUM(IF(cfs = 2 AND spend > 0, spend, 0)) as spend')
            ->where('cfs', 2)
            ->when(in_array($campusFlag, [DM_SUMMARY_FLAG, RVP_SUMMARY_FLAG, COMPANY_SUMMARY_FLAG]),
                fn($q) => $q->whereIn('p.processing_year', $dates),
                fn($q) => $q->whereIn('p.processing_month_date', $dates)
            )
            ->whereIn('p.financial_code', $costCenters)
            ->join(DB::raw('(SELECT cost_center, account_id FROM cafes GROUP BY cost_center, account_id) as c'), 'c.cost_center', '=', 'p.financial_code')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
            ->groupBy('p.financial_code','a.account_id', 'a.name')
            ->get();
    
        foreach ($rows as $row) {
            $finalData[] = [
                'account_id'   => $row->account_id,
                'account_name' => $row->name,
                'cfs'          => $formatSpend($row->spend),
            ];
        }
    
        return $finalData;
    }
    
    static function getNonComplaintCfs($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage){
      
        $query = DB::table('purchases')
            ->select([
                'mfrItem_code',
                'mfrItem_description',
                'manufacturer_name',
                'mfrItem_brand_name',
                'mfrItem_min',
                'distributor_name',
                'mfrItem_parent_category_name',
                DB::raw('SUM(spend) as spend')
            ])
            ->where('spend', '>', 0)
            ->where('cfs', 2)
            ->whereIn('financial_code', $costCenter)->when(in_array($campusFlag, [CAMPUS_FLAG, ACCOUNT_FLAG, DM_FLAG, RVP_FLAG, COMPANY_FLAG]), function ($query) use ($date) {
                return $query->whereIn('processing_month_date', $date);
            }, function ($query) use ($year) {
                return $query->where('processing_year', $year);
            })
            ->groupBy('mfrItem_code', 'mfrItem_description',
            'manufacturer_name',
            'mfrItem_brand_name',
            'mfrItem_min',
            'distributor_name',
            'mfrItem_parent_category_name')
            ->orderByDesc('spend')
            ->paginate($perPage, ['*'], 'page', $page); 
        return $query;
    }
    static function getCfsLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $page, $perPage)
    {
        DB::enableQueryLog();
        $query = DB::table('purchases')
            ->select(
                'mfrItem_code',
                'mfrItem_description',
                'manufacturer_name',
                'mfrItem_brand_name',
                'mfrItem_min',
                'distributor_name',
                DB::raw('SUM(spend) as spend')
            )
            ->whereIn('financial_code', $costCenter)
            ->where('cfs', 2)
            ->where('spend', '>', 0);
    
        if (!empty($mfrItemCode)) {
            $query->where('mfrItem_code', $mfrItemCode);
        }
        if (in_array($campusFlag, [CAMPUS_FLAG, ACCOUNT_FLAG, DM_FLAG, RVP_FLAG, COMPANY_FLAG])) {
            $query->whereIn('processing_month_date', $date);
        } else {
            $query->where('processing_year', $year);
        }
        $result = $query
            ->groupBy('mfrItem_code',
            'mfrItem_description',
            'manufacturer_name',
            'mfrItem_brand_name',
            'mfrItem_min',
            'distributor_name')
            ->orderByDesc('spend')
            ->paginate($perPage, ['*'], 'page', $page); 
        return $result;
    }
    static function getAccountCfsLineItems($costCenter, $date, $year, $campusFlag, $type, $teamName, $mfrItemCode, $page, $perPage){
        
        $query = DB::table('purchases as p')
            ->select([
                'mfrItem_code',
                'mfrItem_description',
                'manufacturer_name',
                'mfrItem_brand_name',
                'mfrItem_min',
                'distributor_name',
                'a.account_id',
                'a.name',
                'p.financial_code',
                DB::raw('SUM(spend) as spend')
            ])
            ->whereIn('financial_code', $costCenter)
            ->where('cfs', 2)
            ->where('mfrItem_code', $mfrItemCode);

        // Conditional on campus_flag
        if (in_array($campusFlag, [CAMPUS_FLAG, ACCOUNT_FLAG, DM_FLAG, RVP_FLAG, COMPANY_FLAG])) {
            $query->whereIn('processing_month_date', $date);
        } else {
            $query->where('processing_year', $year);
        }

        // Subquery join
        $query->joinSub(
            DB::table('cafes')
                ->select('cost_center', 'account_id')
                ->groupBy('cost_center', 'account_id'),
            'c',
            'c.cost_center',
            '=',
            'p.financial_code'
        );

        $query->join('accounts as a', 'a.account_id', '=', 'c.account_id');

        $result = $query
            ->groupBy('mfrItem_code',
            'mfrItem_description',
            'manufacturer_name',
            'mfrItem_brand_name',
            'mfrItem_min',
            'distributor_name',
            'a.account_id',
            'a.name',
            'p.financial_code')
            ->orderByDesc('spend')
            ->get()
            ->map(function ($row) {
                return [
                    'account_id'   => $row->account_id,
                    'account_name' => $row->name,
                    'spend'        => $row->spend,
                ];
            })
            ->toArray();
            return $result;
    }
}
