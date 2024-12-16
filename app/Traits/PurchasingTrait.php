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
            ($section == COFFEE_SPEND && $value >= COFFEE_SPEND_VALUE) ||
            ($section == PRODUCE_DATA && $value >= PRODUCE_DATA_VALUE) || 
            ($section == WHOLE_GRAIN && $value >= WHOLE_GRAIN_VALUE) || 
            ($section == DAIRY && $value >= DAIRY_VALUE) || 
            ($section == ANIMAL_PROTEIN && $value >= ANIMAL_PROTEIN_VALUE) || 
            ($section == PLANT_PROTEIN && $value >= PLANT_PROTEIN_VALUE) || 
            ($section == SUGAR && $value >= SUGAR_VALUE) || 
            ($section == PLANT_OIL && $value >= PLANT_OIL_VALUE)
        ) {
            $color = INDICATOR_POSITIVE;
        } else {
            $color = INDICATOR_NEGATIVE;
        }
        return $color;
    }

    public function get_color_threshold($calculation, $goal, $is_cirlce_fill, $is_goal_greater){
        $color = '';
        $circle_fill = 0;
        if(isset($calculation)){
            if($is_cirlce_fill == TRUE){
                $circle_fill = round($calculation / $goal *100, 1);
            }
            if($is_goal_greater){
                if($calculation >= $goal){
                    $color = '#63BF87';
                    $circle_fill = 'Full';
                } else {
                    $color = '#E5E56B';
                }
            } else {
                if($calculation > $goal){
                    $color = '#E5E56B';
                } else {
                    $color = '#63BF87';
                    $circle_fill = 'Full';
                }
            }
        } else {
            $circle_fill = 'Full';
        }
        return array('color' => $color, 'circle_fill' => $circle_fill);
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
