<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sap extends Model
{
    use HasFactory;

    static function getHierarchyData(){
        $data = DB::table('wn_costcenter as wc')
            ->select(
                'c.cost_center',
                'c.name as cafe_name',
                'al.name as location_name',
                'al.location_id',
                'a.name as account_name',
                'a.account_id',
                'wr.team_description as region_name',
                'wr.team_name as region_team_name',
                'wd.team_description as district_name',
                'wd.team_name as district_team_name',
                'wc.complex_name',
                'wcc.team_description as complex_description'
            )
            ->join('wn_complex as wcc', 'wcc.team_name', '=', 'wc.complex_name')
            ->join('wn_region as wr', 'wr.team_name', '=', 'wc.region_name')
            ->join('wn_district as wd', 'wd.team_name', '=', 'wc.district_name')
            ->join('cafes as c', 'c.cost_center', '=', 'wc.team_name')
            ->join('accounts_locations as al', 'al.location_id', '=', 'c.location_id')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
            ->where('wc.sector_name', 'A00000')
            ->groupBy(
                'c.cost_center',
                'c.name',
                'al.name',
                'al.location_id',
                'a.name',
                'a.account_id',
                'wr.team_description',
                'wr.team_name',
                'wd.team_description',
                'wd.team_name',
                'wc.complex_name',
                'wcc.team_description'
            )
            ->get();
            return $data;
    }
}
