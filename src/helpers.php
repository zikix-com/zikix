<?php

use Illuminate\Http\JsonResponse;
use Zikix\Component\Api;

if (!function_exists('zikix')) {
    /**
     * @param mixed  $data
     * @param string $message
     * @return JsonResponse
     * @throws Exception
     */
    function zikix($data = [], string $message = '')
    {
        return Api::ok($data, $message);
    }
}
