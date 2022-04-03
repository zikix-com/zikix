<?php

namespace Zikix\Zikix\Facades;

use Illuminate\Support\Facades\Facade;

class SlsLogWriter extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sls.writer';
    }
}
