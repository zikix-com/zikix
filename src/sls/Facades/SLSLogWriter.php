<?php

namespace Zikix\Component\SLS\Facades;

use Illuminate\Support\Facades\Facade;

class SLSLogWriter extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sls.writer';
    }
}