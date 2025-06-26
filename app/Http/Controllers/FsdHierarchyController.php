<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FSD;
use DB;

/**
 * FSD Hierarchy Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class FsdHierarchyController extends Controller
{
    /**
     * Get FSD Hierarchy Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with FSD hierarchy data
     * 
     * @api {get} /sector-drop-down-data Get FSD Hierarchy Data
     * @apiName FsdHierarchy
     * @apiGroup FsdHierarchy
     * @apiSuccess {Object} complex Complex level hierarchy data
     * @apiSuccess {Object} rvp RVP level hierarchy data
     * @apiSuccess {Object} dm District manager level hierarchy data
     * @apiSuccess {Object} account Account level hierarchy data
     * @apiSuccess {Object} campus Campus level hierarchy data
     * @apiSuccess {Object} cafe Cafe level hierarchy data
     */
    public function sectorData(){
        $data = FSD::getHierarchyData();
        // Initialize arrays to avoid undefined variable notices
        $complex = [];
        $rvp = [];
        $dm = [];
        $account = [];
        $campus = [];
        $cafe = [];

        // Process data and create associative arrays
        foreach ($data as $val) {
            $complex[$val->complex_name] = [
                'team_name' => $val->complex_name,
                'description' => $val->complex_description,
            ];
            
            $rvp[$val->region_team_name] = [
                'team_name' => $val->region_team_name,
                'description' => $val->region_name,
            ];
            
            $dm[$val->district_team_name] = [
                'team_name' => $val->district_team_name,
                'description' => $val->district_name,
            ];
            
            $account[$val->account_id] = [
                'team_name' => $val->account_id,
                'description' => $val->account_name,
            ];
            
            $campus[$val->location_id] = [
                'team_name' => $val->location_id,
                'description' => $val->location_name,
            ];
            
            $cafe[$val->cost_center] = [
                'team_name' => $val->cost_center,
                'description' => $val->cafe_name,
            ];
        }

        // Create the final associative array
        $fsd = [
            'complex' => array_values($complex),
            'rvp' => array_values($rvp),
            'dm' => array_values($dm),
            'account' => array_values($account),
            'campus' => array_values($campus),
            'cafe' => array_values($cafe),
        ];
        return $this->successResponse($fsd, 'success');
    }
    public function sectorHierarchyData(Request $request){
        $result = FSD::getDropDown($request->type, $request->team_name, $request->rvp, $request->dm, $request->account, $request->campus);
        $hierarchyMap = [
            'rvp' => ['sector', 'dm', 'accounts', 'campuses', 'cafes'],
            'dm' => ['sector', 'rvp', 'accounts', 'campuses', 'cafes'],
            'accounts' => ['sector', 'rvp', 'dm', 'campuses', 'cafes'],
            'campuses' => ['sector', 'rvp', 'dm', 'accounts', 'cafes'],
            'cafes' => ['sector', 'rvp', 'dm', 'accounts', 'campuses'],
        ];
        
        $type = $request->type;
        $validTypes = ['sector', 'rvp', 'dm', 'accounts', 'campuses', 'cafes'];
        
        if (!in_array($type, $validTypes)) {
            return $this->badRequestResponse('Invalid type');
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
        
        return $this->successResponse($finalData, 'success');
    }

    public function accountHierarchy(Request $request){
        $accountId = FSD::getAccountIdByLocation($request->location_id);
        $data = FSD::getAccountTree($accountId);
        return $this->successResponse($data, 'success');
    }

}
