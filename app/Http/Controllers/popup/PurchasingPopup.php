<?php

namespace App\Http\Controllers\popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Popup\PurchasesPopOut;
use App\Traits\PurchasingTrait;

class PurchasingPopup extends Controller
{
    //
    use DateHandlerTrait, PurchasingTrait;

    public function getPopup(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        $record = json_decode(Redis::get($type.'_'.$date[0]), true);
        if(empty($record)){
            
            if($request->type == 'campus'){
                $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
            } else {
                $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
            }
            $corCategories = ['ground_beef', 'chicken', 'turkey', 'pork', 'EGGS_CODE', 'milk_yogurt', 'fish_seafood'];
            $corTotal = PurchasesPopOut::getTotalCor($date, $year, $campusFlag, $type, $costCenter, $corCategories);
            $corTotal = collect($corTotal)->keyBy('account_id')->toArray();
            $cor = PurchasesPopOut::getCor($date, $year, $campusFlag, $type, $costCenter, $corCategories);
            $corList = collect($cor)->keyBy('account_id')->toArray();
            foreach($corList as $data){
                $corList[$data['account_id']]['cor_data']['total'] = $corTotal[$data['account_id']];
            }
            Redis::set($type.'_'.$date[0], json_encode($corList));
            return response()->json([
                'status' => 'success',
                'data' => $corList,
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'data' => $record,
            ], 200);
        }
        
    }

    public function getLineItem(Request $request){
        $year = $request->year;
        $campusFlag = $request->campus_flag;
        $type = $request->type;
        $category = $request->category;
        $page = $request->input('page', 1);                // Default to page 1
        $perPage = $request->input('per_page', 10);
        $categoryCode = $this->getCategoryCode($category);
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        if($request->type == 'campus'){
            $costCenter = json_decode(Redis::get('cost_campus'.$request->team_name), true);
        } else {
            $costCenter = json_decode(Redis::get('cost_'.$request->team_name), true);
        }
        if(!empty($category)){
            $lineItemData = PurchasesPopOut::getLineItem($date, $year, $campusFlag, $type, $costCenter, $categoryCode, $page, $perPage);
        } else {
            $lineItemData = PurchasesPopOut::getTotalLineItem($date, $year, $campusFlag, $type, $costCenter);
        }
        return response()->json([
            'status' => 'success',
            'data' => $lineItemData,
        ], 200);
    }
}
