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
        return prometheus($metrics);
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
