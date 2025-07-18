<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FSD extends Model
{
    //
    static function getSectorDropDownFull(){
        $data = DB::table('wn_costcenter as w')
        ->select(
            'w.team_name', 
            'w.region_name', 
            'w.district_name', 
            'wr.team_description as region_des', 
            'wd.team_description as district_des', 
            'a.name as account_name', 
            'c.account_id', 
            'al.name as location_name', // Renamed from location_id for clarity
            'c.location_id',
            'c.name as cafe_name',
        )
        ->join('cafes as c', 'c.cost_center', '=', 'w.team_name')
        ->join('wn_region as wr', 'wr.team_name', '=', 'w.region_name')
        ->join('wn_district as wd', 'wd.team_name', '=', 'w.district_name')
        ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
        ->join('accounts_locations as al', 'al.location_id', '=', 'c.location_id')
        ->where('c.display_foodstandard', 'Yes')
        ->where('w.sector_name', 'A00000')
        ->groupBy(
            'w.team_name', 
            'w.region_name', 
            'w.district_name', 
            'wr.team_description', // Use the actual column name
            'wd.team_description', // Use the actual column name
            'a.name', // Use the actual column name
            'c.account_id', 
            'al.name', // Use the actual column name
            'c.location_id',
            'c.name'
        )
        ->get();
        return $data;
    }


    static function getDropDown($type, $teamName, $rvp = null, $dm = null, $account = null, $campus = null){
        $data = DB::table('wn_costcenter as w')
            ->select(
                'w.team_name', 
                'w.region_name', 
                'w.district_name',
                'w.sector_name',
                'wr.team_description as region_des', 
                'wd.team_description as district_des', 
                'a.name as account_name', 
                'c.account_id', 
                'al.name as location_name', 
                'c.location_id',
                'c.name as cafe_name'
            )
            ->join('cafes as c', 'c.cost_center', '=', 'w.team_name')
            ->join('wn_region as wr', 'wr.team_name', '=', 'w.region_name')
            ->join('wn_district as wd', 'wd.team_name', '=', 'w.district_name')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id')
            ->join('accounts_locations as al', 'al.location_id', '=', 'c.location_id')
            ->where('c.display_foodstandard', 'Yes')
            ->where('w.sector_name', 'A00000');

        // Apply conditional filter only if $type is 'rvp'
        if ($type === 'rvp') {
            $data->where('w.region_name', $teamName);
            if($dm != null){
                $data->where('w.district_name', $dm);
            }
        }
        if ($type === 'dm') {
            $data->where('w.district_name', $teamName);
            if($rvp != null){
                $data->where('w.region_name', $rvp);
            }
        }
        if ($type === 'account') {
            $data->where('a.account_id', $teamName);
            if($rvp != null){
                $data->where('w.region_name', $rvp);
            }
            if($dm != null){
                $data->where('w.district_name', $dm);
            }
        }
        if ($type === 'campus') {
            $data->where('al.location_id', $teamName);
            if($rvp != null){
                $data->where('w.region_name', $rvp);
            }
            if($dm != null){
                $data->where('w.district_name', $dm);
            }
            if($account != null){
                $data->where('a.account_id', $account);
            }
            
        }
        if ($type === 'cafe') {
            $data->where('c.cost_center', $teamName);
            if($rvp != null){
                $data->where('w.region_name', $rvp);
            }
            if($dm != null){
                $data->where('w.district_name', $dm);
            }
            if($account != null){
                $data->where('a.account_id', $account);
            }
            if($campus != null){
                $data->where('al.location_id', $campus);
            }
        }

        // Group by actual column names instead of aliases
        $data = $data->groupBy(
                'w.team_name', 
                'w.region_name', 
                'w.district_name', 
                'w.sector_name',
                'wr.team_description', 
                'wd.team_description', 
                'a.name', 
                'c.account_id', 
                'al.name', 
                'c.location_id',
                'c.name'
        )->get();
            

        return $data;
    }

    static function getDmDropDownFull($teamName, $type, $cost){
        $data = DB::table('wn_costcenter as w')
            ->select('wd.team_description', 'w.district_name')
            ->join('wn_district as wd', 'wd.team_name', '=', 'w.district_name')
            ->whereIn('w.team_name', $cost)
            ->where('w.sector_name', $teamName)
            ->groupBy('w.district_name', 'wd.team_description')
            ->orderBy('wd.team_description', 'ASC')
            ->get();

            return $data;
    }

    static function getAccountDropDownFull($teamName, $type, $cost){
        $data = DB::table('accounts as a')
            ->select('a.account_id', 'a.name')
            ->join('cafes as c', 'c.account_id', '=', 'a.account_id')
            ->whereIn('c.cost_center', $cost)
            ->groupBy('a.account_id', 'a.name')
            ->orderBy('a.name', 'ASC')
            ->get();
            return $data;
    }
    static function getCampusDropDownFull($teamName, $type, $cost){
        $data = DB::table('accounts_locations as a')
            ->select('a.location_id', 'a.name')
            ->join('cafes as c', 'c.location_id', '=', 'a.location_id')
            ->whereIn('c.cost_center', $cost)
            ->groupBy('a.location_id', 'a.name')
            ->orderBy('a.name', 'ASC')
            ->get();
            return $data;
    }

    static function getAccountTree($accountId){
        $data = DB::table('cafes as c')
            ->select('al.name as location_name', 'al.location_id', 'c.name as cafe_name', 'c.cost_center', 'a.name as account_name', 'a.account_id', 'c.exempt_waste', 'w.content_submitted')
            ->join('accounts_locations as al', 'al.location_id', '=', 'c.location_id')
            ->join('accounts as a', 'a.account_id', '=', 'al.account_id')
            ->leftJoin('waste_profile as w', 'w.costcenter_id', '=', 'c.cost_center')
            ->where('c.account_id', $accountId)
            ->whereNot('c.cost_center', 0)
            ->whereNot('c.cost_center', null)
            ->groupBy('c.cost_center', 'c.name', 'al.location_id', 'a.account_id', 'al.name', 'a.name', 'c.exempt_waste', 'w.content_submitted')
            ->get();

        // Transform into hierarchical array
        $tree = [];

        foreach ($data as $row) {
            // Add account if not exists
            if (!isset($tree[$row->account_id])) {
                $tree[$row->account_id] = [
                    'account_id' => $row->account_id,
                    'account_name' => $row->account_name,
                    'campuses' => []
                ];
            }

            // Add campus if not exists
            if (!isset($tree[$row->account_id]['campuses'][$row->location_id])) {
                $tree[$row->account_id]['campuses'][$row->location_id] = [
                    'location_id' => $row->location_id,
                    'location_name' => $row->location_name,
                    'cafes' => []
                ];
            }
            $border = 'none';
            $message = 'none';
            if($row->content_submitted == 0 && $row->exempt_waste == 'N'){
                $border = 'red';
                $message = 'OOPS: waste programs profile is incomplete';
            }
            // Add cafe under the respective campus
            $tree[$row->account_id]['campuses'][$row->location_id]['cafes'][] = [
                'cost_center' => $row->cost_center,
                'cafe_name' => $row->cafe_name,
                'border' => $border,
                'message' => $message
            ];
        }

        // Convert campuses from associative array to indexed array
        foreach ($tree as &$account) {
            $account['campuses'] = array_values($account['campuses']);
        }
        // Final structured tree
        $tree = array_values($tree);
        return $tree;
    }
    static function getAccountIdByLocation($locationId){
        $account = DB::table('accounts_locations')
        ->select('account_id')
        ->where('location_id', $locationId)
        ->groupBy('account_id')
        ->first();
        return $account->account_id;
    }
}
