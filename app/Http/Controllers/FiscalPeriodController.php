<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FiscalPeriod;

class FiscalPeriodController extends Controller
{
    //
    public function getFiscalYear(Request $request){
        try{
            $data = FiscalPeriod::getyears();
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
    public function getFiscalPeriod(Request $request){
        try{
            $data = FiscalPeriod::getPeriod($request->cost_center = null, $request->year, $request->latest);
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
