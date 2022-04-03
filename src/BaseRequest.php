<?php

namespace Zikix\Zikix;

use Illuminate\Http\Request;

class BaseRequest extends Request
{
    /**
     * @return bool
     */
    public function expectsJson(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function wantsJson(): bool
    {
        return true;
    }
}
