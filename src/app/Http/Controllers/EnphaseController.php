<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Enphase;

class EnphaseController extends Controller
{

    /*
     * The Function to return the metrics from the envoy
     */

    function metrics(){
        $metrics = [];
        $metrics['production'] = Enphase::production();
        $metrics['inverters_status'] = Enphase::invertersStatus();
        return $metrics;
    }
}
