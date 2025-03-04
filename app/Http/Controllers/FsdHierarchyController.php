<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FSD;
use DB;

class FsdHierarchyController extends Controller
{
    //
    public function sectorData(Request $request){
        $result = FSD::getSectorDropDownFull();
        $rvp = [];
        $dm = [];
        $accounts = [];
        $locations = [];
        $cafes = [];

        foreach($result as $val){
            if (!array_key_exists($val->region_name, $rvp)) {
                $rvp[$val->region_name] = array(
                    'team_name' => $val->region_name,
                    'team_description' => $val->region_des,
                );
            }
            if (!array_key_exists($val->district_name, $dm)) {
                $dm[$val->district_name] = array(
                    'team_name' => $val->district_name,
                    'team_description' => $val->district_des,
                );
            }
            if (!array_key_exists($val->account_id, $accounts)) {
                $accounts[$val->account_id] = array(
                    'team_name' => $val->account_id,
                    'team_description' => $val->account_name,
                );
            }
            if (!array_key_exists($val->location_id, $locations)) {
                $locations[$val->location_id] = array(
                    'team_name' => $val->location_id,
                    'team_description' => $val->location_name,
                );
            }
            if (!array_key_exists($val->team_name, $cafes)) {
                $cafes[$val->team_name] = array(
                    'team_name' => $val->team_name,
                    'team_description' => $val->cafe_name,
                );
            }
        }


        $finalData = array(
            'rvp' => $rvp,
            'dm' => $dm,
            'accounts' => $accounts,
            'campuses' => $locations,
            'cafes' => $cafes
        );
        return response()->json([
            'status' => 'success',
            'data' => $finalData,
        ], 200);
    }

    public function sectorHierarchyData(Request $request){
        $result = FSD::getDropDown($request->type, $request->team_name, $request->rvp, $request->dm);
        $hierarchyMap = [
            'rvp' => ['sector', 'dm', 'accounts', 'campuses', 'cafes'],
            'dm' => ['sector', 'rvp', 'accounts', 'campuses', 'cafes'],
            'account' => ['sector', 'rvp', 'dm', 'campuses', 'cafes'],
            'campuses' => ['sector', 'rvp', 'dm', 'accounts', 'cafes'],
            'cafes' => ['sector', 'rvp', 'dm', 'accounts', 'campuses'],
        ];
        
        $type = $request->type;
        $validTypes = ['sector', 'rvp', 'dm', 'account', 'campuse', 'cafe'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        
        // Initialize result arrays dynamically
        $finalData = array_fill_keys($hierarchyMap[$type], []);
        
        foreach ($result as $val) {
            if (in_array('sector', $hierarchyMap[$type]) && !isset($finalData['sector'][$val->sector_name])) {
                $finalData['sector'][$val->sector_name] = [
                    'team_name' => $val->sector_name,
                    'team_description' => 'Bon Appetit',
                ];
            }
            if (in_array('rvp', $hierarchyMap[$type]) && !isset($finalData['rvp'][$val->region_name])) {
                $finalData['rvp'][$val->region_name] = [
                    'team_name' => $val->region_name,
                    'team_description' => $val->region_des,
                ];
            }
            if (in_array('dm', $hierarchyMap[$type]) && !isset($finalData['dm'][$val->district_name])) {
                $finalData['dm'][$val->district_name] = [
                    'team_name' => $val->district_name,
                    'team_description' => $val->district_des,
                ];
            }
            if (in_array('accounts', $hierarchyMap[$type]) && !isset($finalData['accounts'][$val->account_id])) {
                $finalData['accounts'][$val->account_id] = [
                    'team_name' => $val->account_id,
                    'team_description' => $val->account_name,
                ];
            }
            if (in_array('campuses', $hierarchyMap[$type]) && !isset($finalData['campuses'][$val->location_id])) {
                $finalData['campuses'][$val->location_id] = [
                    'team_name' => $val->location_id,
                    'team_description' => $val->location_name,
                ];
            }
            if (in_array('cafes', $hierarchyMap[$type]) && !isset($finalData['cafes'][$val->team_name])) {
                $finalData['cafes'][$val->team_name] = [
                    'team_name' => $val->team_name,
                    'team_description' => $val->cafe_name,
                ];
            }
        }
        
        // Return final data
        
        return response()->json([
            'status' => 'success',
            'data' => $finalData,
        ], 200);
    }

    public function accountHierarchy(Request $request){
        $accountId = FSD::getAccountIdByLocation($request->location_id);
        $data = FSD::getAccountTree($accountId);
    }

}
