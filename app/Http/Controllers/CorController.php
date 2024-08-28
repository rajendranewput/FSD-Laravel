<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;

class CorController extends Controller
{
    use DateHandlerTrait;

    public function CorData(Request $request){
        
        $date = $this->handleDates($request->end_date, $request->campus_flag);
        print_r($date);
        die;
    }
}
