<?php

namespace App\Services\Enphase;

use Illuminate\Support\ServiceProvider;

class EnphaseServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $config = config('services.enphase');

        $this->app->bind('enphase',function() use ($config){
            return new Enphase($config);
        });
    }
}
