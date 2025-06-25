<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    static function getBargraphPopup($costCenter, $year, $date, $fytd, $code){
        $query = DB::table('ledger')
        ->selectRaw('ROUND(SUM(amount), 2) as amount, actual_name')
        ->where('f2f', 1)
        ->when($code == 'incorrectly_code', function ($q) {
            $q->whereNotIn('gl_account', ['411028', '411029', '411031', '411032', '411136', '411138', '411139', '411140', '411141']);
        }, function ($q) use ($code) {
            $q->where('gl_account', $code);
        })
        ->whereIn('unit', $costCenter)
        ->when($fytd, function ($q) use ($year) {
            $q->where('fiscal_year', $year);
        }, function ($q) use ($date) {
            $q->whereIn('fiscal_month', $date);
        })
        ->groupBy('actual_name')
        ->get();
        return $query;
    }
}
