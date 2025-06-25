<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sap;

/**
 * SAP Hierarchy Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class SapHierarchyController extends Controller
{
    /**
     * Get SAP Hierarchy Data
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return JsonResponse JSON response with SAP hierarchy data
     * 
     * @api {get} /sap-hierarchy Get SAP Hierarchy Data
     * @apiName SapHierarchy
     * @apiGroup SapHierarchy
     * @apiSuccess {Object} complex Complex level hierarchy data
     * @apiSuccess {Object} rvp RVP level hierarchy data
     * @apiSuccess {Object} dm District manager level hierarchy data
     * @apiSuccess {Object} account Account level hierarchy data
     * @apiSuccess {Object} campus Campus level hierarchy data
     * @apiSuccess {Object} cafe Cafe level hierarchy data
     */
    public function sapHierarchy(){
        $data = Sap::getHierarchyData();
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
        $sap = [
            'complex' => array_values($complex),
            'rvp' => array_values($rvp),
            'dm' => array_values($dm),
            'account' => array_values($account),
            'campus' => array_values($campus),
            'cafe' => array_values($cafe),
        ];
        return $this->successResponse($sap, 'success');
    }
}
