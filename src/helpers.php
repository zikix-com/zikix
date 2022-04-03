<?php

use Illuminate\Database\Eloquent\Builder;
use Zikix\Zikix\Adb;
use Zikix\Zikix\Model;
use Zikix\Zikix\Where;

if (!function_exists('zikix_adb')) {
    /**
     * @param string $table
     * @param string $connection
     *
     * @return \Illuminate\Database\Query\Builder
     */
    function zikix_adb(string $table, string $connection = 'adb'): \Illuminate\Database\Query\Builder
    {
        return Adb::table($table, $connection);
    }
}

if (!function_exists('zikix_where')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $key
     * @param array $columns
     * @param string $opt
     */
    function zikix_where($builder, string $key, array $columns = [], string $opt = '='): void
    {
        if ($columns === []) {
            Where::query($builder, $key, [$key], $opt);
        } else {
            Where::query($builder, $key, $columns, $opt);
        }
    }
}

if (!function_exists('zikix_like')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $key
     * @param array $columns
     */
    function zikix_like($builder, string $key, array $columns = []): void
    {
        if ($columns === []) {
            Where::query($builder, $key, [$key]);
        } else {
            Where::query($builder, $key, $columns);
        }
    }
}

if (!function_exists('zikix_between')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $column
     * @param string|null $start
     * @param string|null $end
     */
    function zikix_between($builder, string $column = 'created_at', string $start = null, string $end = null): void
    {
        Where::between($builder, $column, $start, $end);
    }
}

if (!function_exists('zikix_has')) {

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request
     * @param string|null $attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function zikix_has(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request,
        string                              $attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {
        return Model::has($model, $request, $attribute, $default);
    }
}

if (!function_exists('zikix_filled')) {

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request
     * @param string|null $attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function zikix_filled(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request,
        string                              $attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {
        return Model::filled($model, $request, $attribute, $default);
    }
}

if (!function_exists('array_change_key_case_recursive')) {
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
