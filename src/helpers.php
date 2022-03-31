<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Zikix\Component\Adb;
use Zikix\Component\Api;
use Zikix\Component\Where;

if (!function_exists('adb')) {
    /**
     * @param string $table
     * @param string $connection
     *
     * @return \Illuminate\Database\Query\Builder
     */
    function adb(string $table, string $connection = 'adb'): \Illuminate\Database\Query\Builder
    {
        return Adb::table($table, $connection);
    }
}

if (!function_exists('where')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $request
     * @param array $columns
     * @param string $opt
     */
    function where($builder, string $request, array $columns = [], string $opt = '='): void
    {
        if ($columns === []) {
            Where::query($builder, $request, [$request], $opt);
        } else {
            Where::query($builder, $request, $columns, $opt);
        }
    }
}

if (!function_exists('whereLike')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $request
     * @param array $columns
     */
    function whereLike($builder, string $request, array $columns = []): void
    {
        if ($columns === []) {
            Where::query($builder, $request, [$request]);
        } else {
            Where::query($builder, $request, $columns);
        }
    }
}

if (!function_exists('whereBetween')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $column
     * @param string|null $start
     * @param string|null $end
     */
    function whereBetween($builder, string $column = 'created_at', string $start = null, string $end = null): void
    {
        Where::between($builder, $column, $start, $end);
    }
}

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
