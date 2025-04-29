<?php

namespace App\Repositories;

use App\Models\GlCode;
use App\Models\CostCenter;
use App\Models\Cafe;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class FarmToForkRepository
{
    public function getFarmToForkAccountData($costCenters, $exp, $endDate, $campusFlag, $type, $team)
    {
        $query = GlCode::query()
            ->whereIn('exp_1', $exp)
            ->whereIn('unit_id', $costCenters);

        if (in_array($campusFlag, [7, 11])) {
            $query->where('processing_year', $endDate);
        } else {
            $query->where('end_date', $endDate);
        }

        if ($type === 'rvp') {
            $query->selectRaw('gc.unit_id, c.sector_name, c.district_name, a.team_description as name, a.team_name as account_id')
                ->selectRaw('SUM(gc.amount) as amount')
                ->join('wn_costcenter as c', 'c.team_name', '=', 'gc.unit_id')
                ->join('wn_district as a', 'a.team_name', '=', 'c.district_name');
        } elseif ($type === 'sector') {
            $query->selectRaw('gc.unit_id, c.sector_name, c.region_name, a.team_description as name, a.team_name as account_id')
                ->selectRaw('SUM(gc.amount) as amount')
                ->join('wn_costcenter as c', 'c.team_name', '=', 'gc.unit_id')
                ->join('wn_region as a', 'a.team_name', '=', 'c.region_name');
        } else {
            $query->selectRaw('gc.unit_id, a.name, a.account_id, gc.end_date')
                ->selectRaw('SUM(gc.amount) as amount')
                ->join(DB::raw('(SELECT cost_center, account_id FROM cafes GROUP BY cost_center) as c'), 'c.cost_center', '=', 'gc.unit_id')
                ->join('accounts as a', 'a.account_id', '=', 'c.account_id');
        }

        return $query->groupBy('account_id')
            ->orderBy('account_id', 'ASC')
            ->get()
            ->toArray();
    }

    public function getFarmToForkAccountDataYear($costCenters, $exp, $endDate, $fytdPeriods, $campusFlag, $type, $team)
    {
        $query = GlCode::query()
            ->whereIn('exp_1', $exp)
            ->whereIn('unit_id', $costCenters);

        if (in_array($campusFlag, [7, 9, 11])) {
            $query->whereIn('processing_year', $fytdPeriods);
        } else {
            $query->whereIn('end_date', $fytdPeriods);
        }

        $query->selectRaw('gc.unit_id, a.name, a.account_id')
            ->selectRaw('CONCAT(gc.processing_year, "-", a.account_id) AS processing_month_date')
            ->selectRaw('SUM(gc.amount) as amount')
            ->join(DB::raw('(SELECT cost_center, account_id FROM cafes GROUP BY cost_center) as c'), 'c.cost_center', '=', 'gc.unit_id')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id');

        return $query->groupBy('account_id')
            ->orderBy('account_id', 'ASC')
            ->get()
            ->toArray();
    }
}
