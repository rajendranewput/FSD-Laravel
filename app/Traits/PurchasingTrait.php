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
    public function getCategory() {
        $category = array(
            array(
                "name" => "Circle Of Responsibility",
                "key" => "circle",
                "subcategories" => array(
                    array(
                        "name" => "Total",
                        "key" => "total",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Ground Beef",
                        "key" => "ground_beef",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Chicken",
                        "key" => "chicken",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Turkey",
                        "key" => "turkey",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Pork",
                        "key" => "pork",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Eggs",
                        "key" => "eggs",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Milk & Yogurt",
                        "key" => "milk_yogurt",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Fish & Seafood",
                        "key" => "fish_seafood",
                        "is_dummy" => false
                    )
                )
            ),
            array(
                "name" => "Farm To Fork",
                "key" => "farm_to_fork",
                "subcategories" => array(
                    array(
                        "name" => "Period",
                        "key" => "periods",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Year To Date",
                        "key" => "year_to_date",
                        "is_dummy" => false
                    )
                )
            ),
            array(
                "name" => "Cooked From Scratch",
                "key" => "cooked_scratch",
                "subcategories" => array(
                    array(
                        "name" => "Cooked",
                        "key" => "cooked",
                        "is_dummy" => true
                    )
                )
            ),
            array(
                "name" => "Leakage From Reporting Vendors",
                "key" => "leakage_vendor",
                "subcategories" => array(
                    array(
                        "name" => "Leakage",
                        "key" => "leakage",
                        "is_dummy" => true
                    )
                )
            ),
            array(
                "name" => "Purchasing Data Visibility",
                "key" => "purchasing_data_visibility",
                "subcategories" => array(
                    array(
                        "name" => "Pdv Total",
                        "key" => "pdv_total",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Seafood",
                        "key" => "seafood",
                        "is_dummy" => false
                    ),
                    array(
                        "name" => "Meat",
                        "key" => "meat",
                        "is_dummy" => false
                    )
                )
            ),
        );

        return $category;

    }
}
