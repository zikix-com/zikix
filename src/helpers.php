<?php

use Illuminate\Http\JsonResponse;
use Zikix\Component\Api;

if (!function_exists('zikix')) {
    /**
     * @param $array
     * @param int $case
     *
     * @return array
     */
    function array_change_key_case_recursive(&$array, int $case = CASE_LOWER)
    {
        $array = array_change_key_case($array, $case);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_change_key_case_recursive($array[$key], $case);
            }
        }

        return $array;
    }
}

if (!function_exists('zikix')) {
    /**
     * @param mixed|array|object $data
     * @param string $message
     *
     * @return JsonResponse
     * @throws Exception
     */
    function zikix($data = [], string $message = ''): JsonResponse
    {
        return Api::ok($data, $message);
    }
}
