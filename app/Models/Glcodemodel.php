<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Glcodemodel extends Model
{
    //
    static function getBargraphData($costCenter, $year, $date, $fytd){
        $gl_account_map = [
            '411028' => 'Produce',
            '411029' => 'Meat',
            '411031' => 'Cheese',
            '411032' => 'Fluid Dairy',
            '411136' => 'Seafood',
            '411138' => 'Bakery',
            '411139' => 'Artisan Other',
            '411140' => 'Coffee',
            '411141' => 'Locally Crafted',
        ];

        // First Query: Fetch F2F category amounts
        $query = DB::table('ledger')
            ->selectRaw('SUM(amount) as amount, gl_account')
            ->where('f2f', 1)
            ->whereIn('gl_account', array_keys($gl_account_map))
            ->whereIn('unit', $costCenter)
            ->when($fytd, function ($q) use ($year) {
                $q->where('fiscal_year', $year);
            }, function ($q) use ($date) {
                $q->whereIn('fiscal_month', $date);
            })
            ->groupBy('gl_account')
            ->get();


        $final_data = [];

        // Map results to category names
        foreach ($query as $row) {
            $code = $row->gl_account;
            if (isset($gl_account_map[$code])) {
                $final_data[$gl_account_map[$code]] = [
                    'key'    => $gl_account_map[$code],
                    'code'   => $code,
                    'amount' => round($row->amount)
                ];
            }
        }

        // Second Query: Fetch incorrectly coded F2F
        $incorrectly_coded = DB::table('ledger')
            ->selectRaw('SUM(amount) as amount')
            ->where('f2f', 1)
            ->whereNotIn('gl_account', array_keys($gl_account_map))
            ->whereIn('unit', $costCenter)
            ->when($fytd, function ($q) use ($year) {
                $q->where('fiscal_year', $year);
            }, function ($q) use ($date) {
                $q->whereIn('fiscal_month', $date);
            })
            ->first();

        $final_data['incorrectly_coded_f2f'] = isset($incorrectly_coded->amount) ? (float) $incorrectly_coded->amount : 0;

        return $final_data;

    }
}
