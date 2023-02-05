<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Enphase extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'enphase';
    }
}
