<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class WBI extends Model
{
    use HasFactory;

    static function getWBIData($date, $costCenter, $campusFlag, $year, $fytd){

        try{
            DB::enableQueryLog();

            
            $query = DB::table('wbi_calculation_data')
            ->select([
                DB::raw('SUM(total_specials) as total_specials'),
                DB::raw('SUM(vegan_items) as vegan_items'),
                DB::raw('SUM(vegetarian_items) as vegetarian_items'),
                DB::raw('SUM(wbi_score) as wbi_score'),
                DB::raw('SUM(wbi_score_min) as wbi_score_min'),
                DB::raw('SUM(wbi_score_max) as wbi_score_max'),
                DB::raw('SUM(green) as green'),
                DB::raw('SUM(yellow) as yellow'),
                DB::raw('SUM(red) as red'),
                DB::raw('SUM(calories_value) as calories_value'),
                DB::raw('SUM(calories_min) as calories_min'),
                DB::raw('SUM(calories_max) as calories_max'),
                'calories_range', // Grouped column
                DB::raw('SUM(sodium_value) as sodium_value'),
                DB::raw('SUM(sodium_min) as sodium_min'),
                DB::raw('SUM(sodium_max) as sodium_max'),
                'sodium_range', // Grouped column
                DB::raw('SUM(sugar_value) as sugar_value'),
                DB::raw('SUM(sugar_min) as sugar_min'),
                DB::raw('SUM(sugar_max) as sugar_max'),
                'sugar_range',  // Grouped column
                'sugar_unit',
                DB::raw('SUM(fruits_weight) as fruits_weight'),
                'fruits_unit',
                DB::raw('SUM(plant_protein_weight) as plant_protein_weight'),
                'plant_protein_unit',
                DB::raw('SUM(whole_grain_item_with) as whole_grain_item_with'),
                DB::raw('SUM(whole_grain_item_without) as whole_grain_item_without')
            ])
            ->groupBy([
                'calories_range',
                'sodium_range',
                'sugar_range',
                'sugar_unit',
                'fruits_unit',
                'plant_protein_unit'
            ])
            ->whereIn('cost_center', $costCenter);
            if ($fytd) {
                $query->where('fiscal_year', $year);
            } else {
                $query->whereIn('end_date', $date);
            }
            // and then you can get query log
            
            $result = $query->get()->toArray();
            // dd(DB::getQueryLog());
            // die;
            //print_r($result);die;
            $wbi = [];
            $calories = [];
            $sodium = [];
            $sugar = [];
            $fruits_vegetables = [];
            $plant_protein = [];
            $whole_grain = [];
//print_r($result[0]);die;
            if(!empty($result)){
                $result = (array) $result[0];
                $greenArray = array(
                    'outer_value' => $result['green'],
                    'inner_percentage' => self::getPercentage($result['green'], $result['total_specials']),
                );
                $yellowArray = array(
                    'outer_value' => $result['yellow'],
                    'inner_percentage' => self::getPercentage($result['yellow'], $result['total_specials']),
                );
                $redArray = array(
                    'outer_value' => $result['red'],
                    'inner_percentage' => self::getPercentage($result['red'], $result['total_specials']),
                );
                $items = array(
                    'green' => $greenArray,
                    'yellow' => $yellowArray,
                    'red' => $redArray
                );
                $wbi = array(
                    'value' => !empty($result['total_specials']) ? round($result['wbi_score']/$result['total_specials'], 1) : null,
                    'value_min' => isset($result['wbi_score_min']) ? round($result['wbi_score_min'], 1) : null,
                    'value_max' => isset($result['wbi_score_max']) ? round($result['wbi_score_max'], 1) : null,
                    'menu_mix' => $items
                );
                $calories = array(
                    'value' => !empty($result['total_specials']) ? ceil($result['calories_value']/$result['total_specials']) : null,
                    'value_min' => isset($result['calories_min']) ? round($result['calories_min'], 1) : null ,
                    'value_max' =>  isset($result['calories_max']) ? round($result['calories_max'], 1) : null ,
                    'range' => $result['calories_range']
                );
                $sodium = array(
                    'value' => !empty($result['total_specials']) ? ceil($result['sodium_value']/$result['total_specials']) : null ,
                    'value_min' => isset($result['sodium_min']) ? round($result['sodium_min'], 1) : null ,
                    'value_max' => isset($result['sodium_max']) ? round($result['sodium_max'], 1) : null ,
                    'range' => $result['sodium_range']
                );
                $sugar = array(
                    'value' => !empty($result['total_specials']) ? round($result['sugar_value']/$result['total_specials'], 1) : null,
                    'value_min' => isset($result['sugar_min']) ? round($result['sugar_min'], 1) : null,
                    'value_max' => isset($result['sugar_max']) ? round($result['sugar_max'], 1) : null,
                    'range' => $result['sugar_range']
                );
                $fruits_vegetables = array(
                    'weight' => !empty($result['total_specials']) ? round($result['fruits_weight']/$result['total_specials'], 1) : null ,
                );
                $sum_vegan_vegi = $result['vegetarian_items'] + $result['vegan_items'];
                $plant_protein = array(
                    'weight' => !empty($sum_vegan_vegi) ? round($result['plant_protein_weight']/$sum_vegan_vegi, 1) : null,
                );
                $whole_grain = array(
                    'items_with' => $result['whole_grain_item_with'],
                    'items_without' => $result['whole_grain_item_without'],
                    'whole_grain_percentage' => self::getWholeGrainPercentage($result['whole_grain_item_with'], $result['whole_grain_item_without']),
                );
            }
            $wbi_section_data = array(
                'wbi' => $wbi,
                'calories' => $calories,
                'sodium' => $sodium,
                'sugar' => $sugar,
                'fruits_vegetables' => $fruits_vegetables,
                'plant_protein' => $plant_protein,
                'whole_grain' => $whole_grain
            );
            return $wbi_section_data;
            

        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    static function getPercentage($item1, $item2){
        if(isset($item2)){
            return round($item1/$item2*100);
        } else {
            return null;
        }
    }
    static function getWholeGrainPercentage($item1, $item2){
        if(isset($item1) && isset($item2)){
            return round($item1/($item1+$item2)*100);
        } else {
            return null;
        }
    }
}