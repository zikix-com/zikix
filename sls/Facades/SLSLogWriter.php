<?php

namespace Zikix\SLS\Facades;

use Illuminate\Support\Facades\Facade;

class SLSLogWriter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sls.writer';
    }
}