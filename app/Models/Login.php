<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Login extends Model
{
    use HasFactory;
    static function getSectorCostCenters($teamName){
        $data = DB::table('wn_costcenter as w')
        ->select('w.team_name')
        ->join('cafes as c', function($join) {
            $join->on('w.team_name', '=', 'c.cost_center')
                ->where('c.display_foodstandard', '=', 'Yes');
        })
        ->where('w.sector_name', 'A00000')
        ->whereNotIn('w.region_name', EXCLUDED_COSTCENTERS)
        ->groupBy('w.team_name')
        ->get();
        return $data;
    }

    static function getSectorRvp($sectorCostCenter){
        $data = DB::table('wn_costcenter as w')
        ->select('r.team_name')
        ->leftJoin('wn_region as r', 'r.team_name', '=', 'w.region_name')
        ->whereIn('w.team_name', $sectorCostCenter)
        ->groupBy('r.team_name')
        ->get();
        return $data;
    }
    static function getRvpCostCenter($rvpName){
        
        $rvp = DB::table('wn_costcenter as w')
        ->select('w.team_name', 'w.region_name')
        ->Join('wn_region as r', 'r.team_name', '=', 'w.region_name')
        ->join('cafes as c', function($join) {
            $join->on('w.team_name', '=', 'c.cost_center')
                ->where('c.display_foodstandard', '=', 'Yes');
        })
        ->whereIn('r.team_name', $rvpName)
        ->groupBy('w.team_name')
        ->groupBy('w.region_name')
        ->get();
        
        return $rvp;
    }

    static function getSectorDm($sectorCostCenter){
        $data = DB::table('wn_costcenter as w')
        ->select('d.team_name')
        ->leftJoin('wn_district as d', 'd.team_name', '=', 'w.district_name')
        ->whereIn('w.team_name', $sectorCostCenter)
        ->groupBy('d.team_name')
        ->get();
        
        return $data;
    }
    static function getDmCostCenter($dmName){
       
        $dm = DB::table('wn_costcenter as w')
        ->select('w.team_name', 'w.district_name')
        ->Join('wn_district as d', 'd.team_name', '=', 'w.district_name')
        ->join('cafes as c', function($join) {
            $join->on('w.team_name', '=', 'c.cost_center')
                ->where('c.display_foodstandard', '=', 'Yes');
        })
        ->whereIn('d.team_name', $dmName)
        ->groupBy('w.team_name')
        ->groupBy('w.district_name')
        ->get();
        
        return $dm;
    }
    static function getSectorAccounts($sectorCostCenter){
        $data = DB::table('accounts as a')
        ->select('a.account_id')
        ->leftJoin('cafes as c', 'c.account_id', '=', 'a.account_id')
        ->whereIn('c.cost_center', $sectorCostCenter)
        ->groupBy('a.account_id')
        ->get();
        
        return $data;
    }
    static function getAccountCostCenter($accountId){
        $data = DB::table('cafes as c')
        ->select('c.cost_center', 'c.account_id')
        ->whereIn('c.account_id', $accountId)
        ->where('c.display_foodstandard', '=', 'Yes')
        ->where('c.cost_center', '!=', '0')
        ->groupBy('c.account_id')
        ->groupBy('c.cost_center')
        ->get();
        
        return $data;
    }
    static function getSectorCampus($sectorCostCenter){
        $data = DB::table('accounts_locations as a')
        ->select('a.location_id')
        ->leftJoin('cafes as c', 'c.location_id', '=', 'a.location_id')
        ->whereIn('c.cost_center', $sectorCostCenter)
        ->groupBy('a.location_id')
        ->get();
        
        return $data;
    }
    static function getCampusCostCenter($campusId){
        $data = DB::table('cafes as c')
        ->select('c.cost_center', 'c.location_id')
        ->whereIn('c.location_id', $campusId)
        ->where('c.display_foodstandard', '=', 'Yes')
        ->where('c.cost_center', '!=', '0')
        ->groupBy('c.location_id')
        ->groupBy('c.cost_center')
        ->get();
        
        return $data;
    }
    
}
