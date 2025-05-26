<?php

namespace App\Models\Popup;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class LeakagePopup extends Model
{
    use HasFactory;
    protected $table = 'leakages';
    protected $fillable = [
        'mfr_prod_desc',
        'mfr_name',
        'mfr_brand',
        'min',
        'dc_name',
        'invoice_din',
        'item_category',
        'leakage_total_spend',
        'unit',
        'end_date',
        'processing_year',
        'is_deleted',
    ];
    static function getAccountLeakage($costCenters, $date, $campusFlag, $type, $teamName)
    {
        // $costCenters = explode(',', $costCenter);
        $dates = is_array($date) ? $date : [$date];
       // $costCenters = explode(',', $cost_center_csv);
        $finalData = [];
    
        $formatSpend = fn($val) => is_numeric($val) ? round($val) : null;
    
        if ($type === 'rvp') {
            $query = DB::table('leakages as l')
                ->select([
                    'l.unit',
                    'c.sector_name',
                    'c.district_name',
                    'a.team_description as name',
                    'a.team_name as account_id',
                ])
                ->selectRaw('SUM(leakage_total_spend) as leakage_total_spend')
                // use conditional whereIn calls, not array for column
                ->when(in_array($campusFlag, [DM_SUMMARY_FLAG, RVP_SUMMARY_FLAG, COMPANY_SUMMARY_FLAG]),
                    fn($q) => $q->whereIn('l.processing_year', $dates),
                    fn($q) => $q->whereIn('l.end_date', $dates)
                )
                ->whereIn('l.unit', $costCenters)
                ->join('wn_costcenter as c', 'c.team_name', '=', 'l.unit')
                ->join('wn_district as a', 'a.team_name', '=', 'c.district_name')
                ->where('c.sector_name', 'A00000')
               // ->when($team !== 'A00000' && $team !== '', fn($q) => $q->where('c.region_name', $team))
                ->groupBy('l.unit',
                'c.sector_name',
                'c.district_name',
                'a.team_description',
                'a.team_name')
                ->get();
    
            foreach ($query as $row) {
                $finalData[] = [
                    'account_id'   => $row->account_id,
                    'account_name' => $row->name,
                    'leakage'          => $formatSpend($row->leakage_total_spend),
                ];
            }
    
            return $finalData;
        }
    
        if ($type === 'sector') {
            $rows = DB::table('leakages as l')
                ->select(['l.unit', 'a.team_description as name', 'a.team_name as account_id'])
                ->selectRaw('SUM(leakage_total_spend) as leakage_total_spend')
                ->when($campusFlag === COMPANY_SUMMARY_FLAG,
                    fn($q) => $q->whereIn('l.processing_year', $dates),
                    fn($q) => $q->whereIn('l.end_date', $dates)
                )
                ->whereIn('l.unit', $costCenters)
                ->join(DB::raw('(SELECT team_name, region_name FROM wn_costcenter GROUP BY team_name, region_name) as c'), 'c.team_name', '=', 'l.unit')
                ->join('wn_region as a', 'a.team_name', '=', 'c.region_name')
                ->groupBy([
                    'a.team_name',
                    'l.unit',
                    'a.team_description',
                ])
                ->orderBy('a.team_name')
                ->get();
    
            foreach ($rows as $row) {
                $finalData[] = [
                    'account_id'   => $row->account_id,
                    'account_name' => $row->name,
                    'leakage'          => $formatSpend($row->leakage_total_spend),
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
    
        $rows = DB::table('leakages as l')
            ->select(['l.unit', 'a.account_id', 'a.name'])
            ->selectRaw('SUM(leakage_total_spend) as leakage_total_spend')
            ->when(in_array($campusFlag, [DM_SUMMARY_FLAG, RVP_SUMMARY_FLAG, COMPANY_SUMMARY_FLAG]),
                fn($q) => $q->whereIn('l.processing_year', $dates),
                fn($q) => $q->whereIn('l.end_date', $dates)
            )
            ->whereIn('l.unit', $costCenters)
            ->join(DB::raw('(SELECT cost_center, account_id FROM cafes GROUP BY cost_center, account_id) as c'), 'c.cost_center', '=', 'l.unit')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
            ->groupBy('l.unit', 'a.account_id', 'a.name')
            ->get();
    
        foreach ($rows as $row) {
            $finalData[] = [
                'account_id'   => $row->account_id,
                'account_name' => $row->name,
                'leakage'          => $formatSpend($row->leakage_total_spend),
            ];
        }
    
        return $finalData;
    }

    static function getNonComplaintData($costCenter, $date, $campusFlag, $type, $teamName, $page, $perPage){
        $query = self::select([
            'id',
            'mfr_prod_desc',
            'mfr_name',
            'mfr_brand',
            'min',
            'dc_name',
            'invoice_din',
            'item_category',
            DB::raw('SUM(leakage_total_spend) as leakage_total_spend')
        ])
        ->whereIn('unit', $costCenter);
        if (in_array($campusFlag, [CAMPUS_FLAG, ACCOUNT_FLAG, DM_FLAG, RVP_FLAG, COMPANY_FLAG])) {
            $query->whereIn('end_date', $date);
        } else {
            $query->where('processing_year', $year);
        }
        $result = $query->groupBy([
            'id',
            'mfr_prod_desc',
            'mfr_name',
            'mfr_brand',
            'min',
            'dc_name',
            'invoice_din',
            'item_category'
        ])
        ->orderBy('leakage_total_spend', 'DESC')
        ->paginate($perPage, ['*'], 'page', $page);
        return $result;
    }
}
