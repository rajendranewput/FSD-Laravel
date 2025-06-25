<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;
use Illuminate\Support\Facades\Redis;


class LoginController extends Controller
{
    //
    public function setCostCentersToRedis(Request $request){
        $sectorCost = Login::getSectorCostCenters($request->team_name);
        foreach($sectorCost as $value){
            $sectorCostCenter[] = $value->team_name;
            Redis::set('cost_'.$value->team_name, json_encode($value->team_name));
        }
      
        Redis::set('cost_A00000', json_encode($sectorCostCenter));
        
        //rvp cost center
        $sectorRvp = Login::getSectorRvp($sectorCostCenter);
        foreach($sectorRvp as $rvpValue){
            $rvpTeamName[] = $rvpValue->team_name;
        }
        $rvpCostCenter = Login::getRvpCostCenter($rvpTeamName);
        foreach($rvpCostCenter as $key => $rvp){
            $cost[$rvp->region_name][] = $rvp->team_name;
        }
        foreach($rvpTeamName as $rvpVal){
            Redis::set('cost_'.$rvpVal, json_encode($cost[$rvpVal]));
        }
        //dm costcenter
        $sectorDm = Login::getSectorDm($sectorCostCenter);
        foreach($sectorDm as $dmValue){
            $dmTeamName[] = $dmValue->team_name;
        }
        $dmCostCenter = Login::getDmCostCenter($dmTeamName);
        foreach($dmCostCenter as $key => $dm){
            $dmCost[$dm->district_name][] = $dm->team_name;
        }
        foreach($dmTeamName as $dmVal){
            if (isset($dmCost[$dmVal])) {
                Redis::set('cost_'.$dmVal, json_encode($dmCost[$dmVal]));
            } else {
                Redis::set('cost_'.$dmVal, null);
            }
        }

        //accounts costcenter
        $sectorAccount = Login::getSectorAccounts($sectorCostCenter);
        foreach($sectorAccount as $accountValue){
            $accountTeamName[] = $accountValue->account_id;
        }
        $accountCostCenter = Login::getAccountCostCenter($accountTeamName);
        foreach($accountCostCenter as $key => $account){
            $accountCost[$account->account_id][] = $account->cost_center;
        }
        
        foreach($accountTeamName as $accountVal){
            Redis::set('cost_'.$accountVal, json_encode($accountCost[$accountVal]));
        }

        //campus costcenter
        $sectorCampus = Login::getSectorCampus($sectorCostCenter);
        foreach($sectorCampus as $campusValue){
            $campusTeamName[] = $campusValue->location_id;
        }
        $campusCostCenter = Login::getCampusCostCenter($campusTeamName);
        foreach($campusCostCenter as $key => $campus){
            $campusCost[$campus->location_id][] = $campus->cost_center;
        }
        
        foreach($campusTeamName as $campusVal){
            Redis::set('cost_campus'.$campusVal, json_encode($campusCost[$campusVal]));
        }
        return true;
    }
}

