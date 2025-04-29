<?php

namespace App\Models\Popup;

use Illuminate\Database\Eloquent\Model;
use DB;

class FarmToForkModel extends Model
{
    //
    static function getFarmToForkPop($cost_centers, $exp, $end_date, $campus_flag, $type, $team)
    {
        if ($type === 'rvp') {
            $query = DB::table('gl_codes as gc')
                ->select(
                    'gc.unit_id',
                    'c.sector_name',
                    'c.district_name',
                    DB::raw('a.team_description as name'),
                    DB::raw('a.team_name as account_id'),
                    DB::raw("CONCAT(gc.processing_date, '-', a.team_name) as processing_month_date"),
                    DB::raw('SUM(gc.amount) as amount')
                )
                ->join('wn_costcenter as c', 'c.team_name', '=', 'gc.unit_id')
                ->join('wn_district as a', 'a.team_name', '=', 'c.district_name')
                ->whereIn('gc.exp_1', $exp)
                ->whereIn('gc.unit_id', $cost_centers)
                ->where('c.sector_name', 'A00000');
    
            if (in_array($campus_flag, [7, 11])) {
                $query->where('gc.processing_year', $end_date);
            } else {
                $query->whereIn('gc.processing_date', $end_date);
            }
            if ($team !== 'A00000' && !empty($team)) {
                $query->where('c.region_name', $team);
            }
            return $query
                ->groupBy('gc.unit_id',
                'c.sector_name',
                'c.district_name', 'name', 'account_id', 'processing_month_date')
                ->orderBy('a.team_name', 'ASC')
                ->get();
              

        } elseif ($type === 'sector') {
            $query = DB::table('gl_codes as gc')
                ->select(
                    'gc.unit_id',
                    'c.sector_name',
                    'c.region_name',
                    DB::raw('a.team_description as name'),
                    DB::raw('a.team_name as account_id'),
                    DB::raw("CONCAT(gc.processing_date, '-', a.team_name) as processing_month_date"),
                    DB::raw('SUM(gc.amount) as amount')
                )
                ->join('wn_costcenter as c', 'c.team_name', '=', 'gc.unit_id')
                ->join('wn_region as a', 'a.team_name', '=', 'c.region_name')
                ->whereIn('gc.exp_1', $exp)
                ->whereIn('gc.unit_id', $cost_centers)
                ->where('c.sector_name', 'A00000');
    
            if ($campus_flag == 11) {
                $query->where('gc.processing_year', $end_date);
            } else {
                $query->whereIn('gc.processing_date', $end_date);
            }
    
            return $query
                ->groupBy('gc.unit_id',
                'c.sector_name',
                'c.region_name', 'name', 'account_id', 'processing_month_date')
                ->orderBy('a.team_name', 'ASC')
                ->get();
    
        } else {
            if (!empty($team)) {
                $cost_centers = DB::table('wn_costcenter as w')
                    ->join('cafes as c', 'c.cost_center', '=', 'w.team_name')
                    ->when($team !== 'A00000', function ($query) use ($team) {
                        if (strlen($team) > 5) {
                            $query->where('w.region_name', $team);
                        } else {
                            $query->where('w.district_name', $team);
                        }
                    })
                    ->where('w.sector_name', 'A00000')
                    ->where('display_foodstandard', 1)
                    ->groupBy('c.cost_center')
                    ->pluck('w.team_name');
            }
    
            $query = DB::table('gl_codes as gc')
                ->select(
                    'gc.unit_id',
                    'a.name',
                    'a.account_id',
                    'gc.processing_date',
                    DB::raw("CONCAT(gc.processing_date, '-', a.account_id) as processing_month_date"),
                    DB::raw('SUM(gc.amount) as amount')
                )
                ->join(DB::raw('(SELECT cost_center, account_id FROM cafes GROUP BY cost_center) as c'), 'c.cost_center', '=', 'gc.unit_id')
                ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
                ->whereIn('gc.exp_1', $exp)
                ->whereIn('gc.unit_id', $cost_centers);
    
            if (in_array($campus_flag, [7, 11])) {
                $query->where('gc.processing_year', $end_date);
            } else {
                $query->whereIn('gc.processing_date', $end_date);
            }
    
            return $query
            ->groupBy('gc.unit_id',
                 'name', 'account_id', 'processing_month_date')
                ->orderBy('c.account_id', 'ASC')
                ->get();
        }
    }
    static function getFarmToForkPopYTD($cost_centers, $exp, $end_date, $campus_flag, $ytd, $type, $team){
        if ($type === 'rvp') {
            DB::enableQueryLog();

            $query = DB::table('gl_codes as gc')
                ->select(
                    'gc.unit_id',
                    'c.sector_name',
                    'c.district_name',
                    DB::raw('a.team_description as name'),
                    DB::raw('a.team_name as account_id'),
                    //DB::raw("CONCAT(gc.processing_date, '-', a.team_name) as processing_month_date"),
                    DB::raw('SUM(gc.amount) as amount')
                )
                ->when(
                    in_array($campus_flag, [7, 9, 11]),
                    fn($q) => $q->selectRaw("CONCAT(gc.processing_year, '-', a.team_name) AS processing_month_date"),
                    fn($q) => $q->selectRaw("CONCAT(gc.end_date,       '-', a.team_name) AS processing_month_date")
                )
                ->join('wn_costcenter as c', 'c.team_name', '=', 'gc.unit_id')
                ->join('wn_district as a', 'a.team_name', '=', 'c.district_name')
                ->whereIn('gc.exp_1', $exp)
                ->whereIn('gc.unit_id', $cost_centers)
                ->where('c.sector_name', 'A00000');
    
            if (in_array($campus_flag, [7, 11])) {
                $query->whereIn('gc.processing_year', $ytd);
            } else {
                $query->whereIn('gc.end_date', $ytd);
            }
            if ($team !== 'A00000' && !empty($team)) {
                $query->where('c.region_name', $team);
            }
            return $query
               ->groupBy('gc.unit_id',
                'c.sector_name',
                'c.district_name', 'name', 'account_id', 'processing_month_date')
                ->orderBy('a.team_name', 'ASC')
                ->get();
                //dd(DB::getQueryLog());

        } elseif ($type === 'sector') {
            $query = DB::table('gl_codes as gc')
                ->select(
                    'gc.unit_id',
                    'c.sector_name',
                    'c.region_name',
                    DB::raw('a.team_description as name'),
                    DB::raw('a.team_name as account_id'),
                    //DB::raw("CONCAT(gc.processing_date, '-', a.team_name) as processing_month_date"),
                    DB::raw('SUM(gc.amount) as amount')
                )
                ->when(
                    in_array($campus_flag, [7, 9, 11]),
                    fn($q) => $q->selectRaw("CONCAT(gc.processing_year, '-', a.team_name) AS processing_month_date"),
                    fn($q) => $q->selectRaw("CONCAT(gc.end_date,       '-', a.team_name) AS processing_month_date")
                )
                ->join('wn_costcenter as c', 'c.team_name', '=', 'gc.unit_id')
                ->join('wn_region as a', 'a.team_name', '=', 'c.region_name')
                ->whereIn('gc.exp_1', $exp)
                ->whereIn('gc.unit_id', $cost_centers)
                ->where('c.sector_name', 'A00000');
    
            if ($campus_flag == 11) {
                $query->whereIn('gc.processing_year', $ytd);
            } else {
                $query->whereIn('gc.processing_date', $ytd);
            }
    
            return $query
                ->groupBy('gc.unit_id',
                'c.sector_name',
                'c.region_name', 'name', 'account_id', 'processing_month_date')
                ->orderBy('a.team_name', 'ASC')
                ->get();
    
        } else {
            if (!empty($team)) {
                $cost_centers = DB::table('wn_costcenter as w')
                    ->join('cafes as c', 'c.cost_center', '=', 'w.team_name')
                    ->when($team !== 'A00000', function ($query) use ($team) {
                        if (strlen($team) > 5) {
                            $query->where('w.region_name', $team);
                        } else {
                            $query->where('w.district_name', $team);
                        }
                    })
                    ->where('w.sector_name', 'A00000')
                    ->where('display_foodstandard', 1)
                    ->groupBy('c.cost_center')
                    ->pluck('w.team_name');
            }
    
            $query = DB::table('gl_codes as gc')
                ->select(
                    'gc.unit_id',
                    'a.name',
                    'a.account_id',
                    'gc.processing_date',
                    //DB::raw("CONCAT(gc.processing_date, '-', a.account_id) as processing_month_date"),
                    DB::raw('SUM(gc.amount) as amount')
                )
                ->when(
                    in_array($campus_flag, [7, 9, 11]),
                    fn($q) => $q->selectRaw("CONCAT(gc.processing_year, '-', a.team_name) AS processing_month_date"),
                    fn($q) => $q->selectRaw("CONCAT(gc.end_date,       '-', a.team_name) AS processing_month_date")
                )
                ->join(DB::raw('(SELECT cost_center, account_id FROM cafes GROUP BY cost_center) as c'), 'c.cost_center', '=', 'gc.unit_id')
                ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
                ->whereIn('gc.exp_1', $exp)
                ->whereIn('gc.unit_id', $cost_centers);
    
            if (in_array($campus_flag, [7, 11])) {
                $query->whereIn('gc.processing_year', $ytd);
            } else {
                $query->whereIn('gc.processing_date', $ytd);
            }
    
            return $query
            ->groupBy('gc.unit_id',
                 'name', 'account_id', 'processing_month_date')
                ->orderBy('c.account_id', 'ASC')
                ->get();
        }
    }
    
}
