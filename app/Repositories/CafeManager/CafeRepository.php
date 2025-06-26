<?php

namespace App\Repositories\CafeManager;

use App\Models\cafemanager\Cafe;
use App\Models\CafeManager\MenusItemsMealTypesOption;
use Illuminate\Support\Facades\DB;

class CafeRepository
{
    public function findCafe($cafeId)
    {
        return Cafe::where('cafe_id', $cafeId)->first();
    }

    public function getCustomDayParts($cafeId)
    {
        return MenusItemsMealTypesOption::with('cafe:cafe_id,name')
            ->where('cafe_id', $cafeId)
            ->orderBy('meal_type_id', 'asc')
            ->get();
    }

    public function getCustomDayPart($cafeId)
    {
        return MenusItemsMealTypesOption::with('cafe:cafe_id,name')
            ->where('cafe_id', $cafeId)
            ->orderBy('meal_type_id', 'asc')
            ->first();
    }

    public function saveOrUpdateDayPart(array $request)
    {
        $daypart = [
            'meal_type' => $request['meal_type'],
            'abbreviation' => $request['abbreviation'],
            'no_service' => 0,
            'cafe_id' => $request['cafe_id'],
            'allow_time_overlap' => !empty($request['allow_time_overlap']) ? 1 : 0,
            'allow_multi_occur' => !empty($request['allow_multi_occur']) ? 1 : 0,
            'hide_day_part' => !empty($request['hide_day_part']) ? 1 : 0,
        ];

        return MenusItemsMealTypesOption::updateOrCreate(
            ['meal_type_id' => $request['meal_type_id']],
            $daypart
        );
    }

    public function isDayPartInUse($mealTypeId, $cafeId)
    {
        return DB::table('cafes as c')
            ->join('cafes_stations as s', 'c.cafe_id', '=', 's.cafe_id')
            ->join('menus_items as m', function ($join) use ($mealTypeId) {
                $join->on('s.station_id', '=', 'm.station_id')
                     ->whereRaw("CONCAT('|', m.meal_type_id, '|') LIKE ?", ["%|$mealTypeId|%"]);
            })
            ->where('c.cafe_id', $cafeId)
            ->limit(1)
            ->exists();
    }
}
