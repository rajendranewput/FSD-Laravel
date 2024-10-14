<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sap;

class SapHierarchyController extends Controller
{
    //
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
        return response()->json([
            'message' => 'Success',
            'data' => $sap
        ], 200); 
    }
}
