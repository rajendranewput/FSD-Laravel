<?php

namespace App\Traits;

trait PurchasingTrait
{
    public function getColorThreshold($value, $section){
        if (
            ($section == COR_SECTION && $value >= COR_COLOR_DIVIDE_VALUE) ||
            ($section == COOKED_LEAKAGE_SECTION && $value <= PPS_COLOR_DIVIDE_VALUE) ||
            ($section == FARM_FORK_SECTION && $value >= FF_COLOR_DIVIDE_VALUE) ||
            ($section == IMPORTED_MEAT && $value >= IMPORTED_MEAT_VALUE) ||
            ($section == PAPER_PURCHASES && $value >= PAPER_PURCHASES_VALUE) ||
            ($section == COFFEE_SPEND && $value >= COFFEE_SPEND_VALUE)
        ) {
            $color = INDICATOR_POSITIVE;
        } else {
            $color = INDICATOR_NEGATIVE;
        }
        return $color;
    }

    public function getCorValue($data, $category){
        $firstItem = 0;
        $secondItem = 0;
        foreach($data as $key => $value){
            if($value->mfrItem_parent_category_code == $category){
                if($value->cor == 1){
                    $firstItem += $value->spend;
                }
                if(in_array($value->cor, [1,-1,2])){
                    $secondItem += $value->spend;
                }
            }
        }
        if(empty($firstItem) || empty($secondItem)){
            if(empty($firstItem) && empty($secondItem)){
                $result = null;
            } else {
                $result = 0;
            }
        } else {
            $result = round(($firstItem/$secondItem)*100);
        }
        return $result;
    }
}
