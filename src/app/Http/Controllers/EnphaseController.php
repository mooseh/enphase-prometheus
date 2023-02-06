<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Enphase;
use Arr;

class EnphaseController extends Controller
{

    /*
     * The Function to return the metrics from the envoy
     */

    function metrics(){
        $metrics = [];
        $metrics['production'] = Enphase::production();
        $metrics['inverters'] = collect(Enphase::inverters())->keyBy('serialNumber')->toArray();
    if (request()->has('format') && request()->input('format') == "json") {
        return $metrics;
    }

    return response(prometheus($metrics))->header('Content-type', 'text/plain');
    }

    /**
     * A Method to simulate the envoy pages
     */

    public function envoy()
    {
        $backbone = Enphase::getBackbone();
        return view('envoy.home', compact('backbone'));
    }

    /**
     * A Method to get the enphase Asset
     */

    public function asset()
    {
        $path = ltrim(request()->path(), "envoy");
        return Enphase::getAsset($path);
    }

}
