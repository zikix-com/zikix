<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Zikix\Component\Adb;
use Zikix\Component\Api;
use Zikix\Component\Model;
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
     * @param string $key
     * @param array $columns
     * @param string $opt
     */
    function where($builder, string $key, array $columns = [], string $opt = '='): void
    {
        if ($columns === []) {
            Where::query($builder, $key, [$key], $opt);
        } else {
            Where::query($builder, $key, $columns, $opt);
        }
    }
}

if (!function_exists('whereLike')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $key
     * @param array $columns
     */
    function whereLike($builder, string $key, array $columns = []): void
    {
        if ($columns === []) {
            Where::query($builder, $key, [$key]);
        } else {
            Where::query($builder, $key, $columns);
        }
    }
}

if (!function_exists('like')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $key
     * @param array $columns
     */
    function like($builder, string $key, array $columns = []): void
    {
        if ($columns === []) {
            Where::query($builder, $key, [$key]);
        } else {
            Where::query($builder, $key, $columns);
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

if (!function_exists('between')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $column
     * @param string|null $start
     * @param string|null $end
     */
    function between($builder, string $column = 'created_at', string $start = null, string $end = null): void
    {
        Where::between($builder, $column, $start, $end);
    }
}

if (!function_exists('has')) {

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request
     * @param string|null $attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function has(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request,
        string                              $attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {
        return Model::has($model, $request, $attribute, $default);
    }
}

if (!function_exists('filled')) {

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request
     * @param string|null $attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function filled(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request,
        string                              $attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {
        return Model::filled($model, $request, $attribute, $default);
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
