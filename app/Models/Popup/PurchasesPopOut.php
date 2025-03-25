<?php

namespace App\Models\Popup;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Traits\PurchasingTrait;


class PurchasesPopOut extends Model
{
    //
    use PurchasingTrait;

    static function getTotalCor($endDate, $year, $campusFlag, $type, $costCenter, $corCategories){
        if($type == 'rvp'){
            $data = DB::table('trend_purchasing_' . $year . ' as p')
            ->selectRaw('SUM(first_spend) as first_item, SUM(second_spend) as second_item, wr.team_name as account_id, wr.team_description as name')
            ->whereIn('cost_center', $costCenter)
            ->whereIn('p.category', $corCategories)
            ->join('wn_costcenter as c', 'c.team_name', '=', 'p.cost_center')
            ->join('wn_region as wr', 'wr.team_name', '=', 'c.region_name');
            if(!$campusFlag == 11){
                $data->whereIn('p.end_date', $endDate);
            }   
            $data->groupBy('wr.team_name', 'wr.team_description');
            $result = $data->get();
        } else if($type == 'dm'){
            $data = DB::table('trend_purchasing_' . $year . ' as p')
            ->selectRaw('SUM(first_spend) as first_item, SUM(second_spend) as second_item, wd.team_name as account_id, wd.team_description as name')
            ->whereIn('cost_center', $costCenter)
            ->whereIn('p.category', $corCategories)
            ->join('wn_costcenter as c', 'c.team_name', '=', 'p.cost_center')
            ->join('wn_district as wd', 'wd.team_name', '=', 'c.district_name');
            if(!$campusFlag == 11){
                $data->whereIn('p.end_date', $endDate);
            }   
            $data->groupBy('wd.team_name', 'wd.team_description');
            $result = $data->get();
        } else {
            $data = DB::table('trend_purchasing_' . $year . ' as p')
            ->selectRaw('SUM(first_spend) as first_item, SUM(second_spend) as second_item, a.account_id, a.name')
            ->whereIn('p.cost_center', $costCenter)
            ->whereIn('p.category', $corCategories)
            ->join('cafes as c', 'c.cost_center', '=', 'p.cost_center')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id');
            if(!$campusFlag == 11){
                $data->whereIn('p.end_date', $endDate);
            }   
            $data->groupBy('a.account_id', 'a.name');
            $result = $data->get();
        }
        
            $corData = [];
            foreach ($result as $row) {
                $account_id = $row->account_id;
                $first_spend = $row->first_item;
                $second_spend = $row->second_item;
                
                if(empty($first_spend) || empty($second_spend)){
                    if(empty($first_spend) && empty($second_spend)){
                        $spend = null;
                    } else {
                        $spend = 0;
                    }
                } else {
                    $cal = $first_spend/$second_spend*100;
                    $spend = round(ABS($first_spend/$second_spend*100));
                    if(is_nan($spend)){
                        $spend = null;
                    }
                }
                $disable_link = false;
                    if($cal == 100){
                        $disable_link = true;
                    }
                $corData[$account_id][] = array(
                    'disable_link' => $disable_link,
                    'spend' => $spend,
                );
            }
            $final_data = [];
            $first = collect($result)->keyBy('account_id')->toArray();
            foreach($first as $fkey => $first_val) {
                if($fkey == $first_val->account_id){
                    $final_data[] = array(
                        'account_id' => $first_val->account_id,
                        'account_name' => $first_val->name,
                        'total_data' => $corData[$fkey],
                    );
                }
            }
            return $final_data;
    }

    static function getCor($endDate, $year, $campusFlag, $type, $costCenter, $corCategories){
        if($type == 'rvp'){
            $data = DB::table('trend_purchasing_' . $year . ' as p')
            ->selectRaw('SUM(first_spend) as first_item, SUM(second_spend) as second_item, category, wr.team_name as account_id, wr.team_description as name')
            ->whereIn('cost_center', $costCenter)
            ->whereIn('p.category', $corCategories)
            ->join('wn_costcenter as c', 'c.team_name', '=', 'p.cost_center')
            ->join('wn_region as wr', 'wr.team_name', '=', 'c.region_name');
            if(!$campusFlag == 11){
                $data->whereIn('p.end_date', $endDate);
            }   
            $data->groupBy('p.category', 'wr.team_name', 'wr.team_description');
            $result = $data->get();
        } else if($type == 'dm'){
            $data = DB::table('trend_purchasing_' . $year . ' as p')
            ->selectRaw('SUM(first_spend) as first_item, SUM(second_spend) as second_item, category, wd.team_name as account_id, wd.team_description as name')
            ->whereIn('cost_center', $costCenter)
            ->whereIn('p.category', $corCategories)
            ->join('wn_costcenter as c', 'c.team_name', '=', 'p.cost_center')
            ->join('wn_district as wd', 'wd.team_name', '=', 'c.district_name');
            if(!$campusFlag == 11){
                $data->whereIn('p.end_date', $endDate);
            }   
            $data->groupBy('p.category', 'wd.team_name', 'wd.team_description');
            $result = $data->get();
        } else {
            $data = DB::table('trend_purchasing_' . $year . ' as p')
            ->selectRaw('SUM(first_spend) as first_item, SUM(second_spend) as second_item, category, a.account_id, a.name')
            ->whereIn('p.cost_center', $costCenter)
            ->whereIn('p.category', $corCategories)
            ->join('cafes as c', 'c.cost_center', '=', 'p.cost_center')
            ->join('accounts as a', 'a.account_id', '=', 'c.account_id');
            if(!$campusFlag == 11){
                $data->whereIn('p.end_date', $endDate);
            }   
            $data->groupBy('p.category', 'a.account_id', 'a.name');
            $result = $data->get();
        }
        
            $corData = [];
            foreach ($result as $row) {
                $account_id = $row->account_id;
                $category = $row->category;
                $first_spend = $row->first_item;
                $second_spend = $row->second_item;
                
                if(empty($first_spend) || empty($second_spend)){
                    if(empty($first_spend) && empty($second_spend)){
                        $spend = null;
                    } else {
                        $spend = 0;
                    }
                } else {
                    $cal = $first_spend/$second_spend*100;
                    $spend = round(ABS($first_spend/$second_spend*100));
                    if(is_nan($spend)){
                        $spend = null;
                    }
                }
                $disable_link = false;
                    if($cal == 100){
                        $disable_link = true;
                    }
                $corData[$account_id][$category][] = array(
                    'disable_link' => $disable_link,
                    'spend' => $spend,
                );
            }
            $final_data = [];
            $first = collect($result)->keyBy('account_id')->toArray();
            foreach($first as $fkey => $first_val) {
                if($fkey == $first_val->account_id){
                    $final_data[] = array(
                        'account_id' => $first_val->account_id,
                        'account_name' => $first_val->name,
                        'cor_data' => $corData[$fkey],
                    );
                }
            }
        return $final_data;
    }

    static function getLineItem($endDate, $year, $campusFlag, $type, $costCenter, $corCategories, $page, $perPage){
          
        $data = DB::table('purchases')
        ->selectRaw('SUM(spend) as spend, mfrItem_code, mfrItem_description, manufacturer_name, mfrItem_brand_name, mfrItem_min, distributor_name')
        ->whereIn('financial_code', $costCenter)
        ->where('mfrItem_parent_category_code', $corCategories)
        ->where('cor', 2)
        ->when(in_array($campusFlag, [CAMPUS_FLAG, ACCOUNT_FLAG, DM_FLAG, RVP_FLAG, COMPANY_FLAG]), function ($query) use ($endDate) {
            return $query->whereIn('processing_month_date', $endDate);
        }, function ($query) use ($year) {
            return $query->where('processing_year', $year);
        })
        ->groupBy('mfrItem_code', 'mfrItem_description', 'manufacturer_name', 'mfrItem_brand_name', 'mfrItem_min', 'distributor_name')
        ->orderByDesc('spend')
        ->paginate($perPage, ['*'], 'page', $page); // Pagination
        return $data;
    }
    static function getTotalLineItem($endDate, $year, $campusFlag, $type, $costCenter){
        $corCategories = [BEEF_CODE, CHICKEN_CODE, TURKEY_CODE, PORK_CODE, EGGS_CODE, DAIRY_PRODUCT_CODE, FISH_AND_SEEFOOD_CODE];
        $data = DB::table('purchases')
            ->selectRaw('SUM(spend) as spend, mfrItem_code, mfrItem_description, manufacturer_name, mfrItem_brand_name, mfrItem_min, distributor_name')
            ->whereIn('financial_code', $costCenter)
            ->whereIn('mfrItem_parent_category_code', $corCategories)
            ->where('cor', 2)
            ->when(in_array($campusFlag, [CAMPUS_FLAG, ACCOUNT_FLAG, DM_FLAG, RVP_FLAG, COMPANY_FLAG]), function ($query) use ($endDate) {
                return $query->whereIn('processing_month_date', $endDate);
            }, function ($query) use ($year) {
                return $query->where('processing_year', $year);
            })
            
            ->groupBy('mfrItem_code', 'mfrItem_description', 'manufacturer_name', 'mfrItem_brand_name', 'mfrItem_min', 'distributor_name')
            ->orderByDesc('spend')
            ->get();

        return $data;
    }
    
}
