<?php

namespace App\Traits;

use DateTime;

trait DateHandlerTrait
{

    public function handleDates($date, $campus_roll_up, $actualDate = false)
    {
        if (strpos($date, ',') !== false) {
            $date = explode(',', $date);
        }

        if (in_array($campus_roll_up, [
            CAMPUS_SUMMARY_FLAG, 
            CAFE_SUMMARY_FLAG, 
            ACCOUNT_SUMMARY_FLAG, 
            DM_SUMMARY_FLAG, 
            RVP_SUMMARY_FLAG, 
            COMPANY_SUMMARY_FLAG
        ])) {
            $end_date = $date;
        } else {
            if($actualDate){
                $end_date = [];
                if (is_array($date)) {
                    foreach ($date as $d_val) {
                        $date = new DateTime($d_val);
                        $end_date[] = $date->format('Y-m-d');
                    }
                } else {
                    $date = new DateTime($date);
                    $end_date[] = $date->format('Y-m-d');
                }
            } else {
                $end_date = [];
                if (is_array($date)) {
                    foreach ($date as $d_val) {
                        $date = new DateTime($d_val);
                        $end_date[] = $date->format('Y-m-01');
                    }
                } else {
                    $date = new DateTime($date);
                    $end_date[] = $date->format('Y-m-01');
                }
            }
            
        }

        return $end_date;
    }
}
