<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Zikix\Zikix\Adb;
use Zikix\Zikix\Model;
use Zikix\Zikix\Where;

if (!function_exists('id')) {
    /**
     * @return int|string|null
     */
    function id()
    {
        return Auth::id();
    }
}

if (!function_exists('user')) {
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null|\App\Models\User
     */
    function user()
    {
        return Auth::user();
    }
}

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
     * @param string $request_key
     * @param array $columns
     * @param string $opt
     */
    function zikix_where($builder, string $request_key, array $columns = [], string $opt = '='): void
    {
        if ($columns === []) {
            Where::query($builder, $request_key, [$request_key], $opt);
        } else {
            Where::query($builder, $request_key, $columns, $opt);
        }
    }
}

if (!function_exists('zikix_like')) {
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $request_key
     * @param array $columns
     * @param string $pre_like
     */
    function zikix_like($builder, string $request_key, array $columns = [], string $pre_like = '%'): void
    {
        if ($columns === []) {
            Where::query($builder, $request_key, [$request_key], 'like', $pre_like);
        } else {
            Where::query($builder, $request_key, $columns, 'like', $pre_like);
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
     * @param string $request_key
     * @param string|null $model_attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function zikix_has(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request_key,
        string                              $model_attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {
        return Model::has($model, $request_key, $model_attribute, $default);
    }
}

if (!function_exists('zikix_filled')) {

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request_key
     * @param string|null $model_attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function zikix_filled(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request_key,
        string                              $model_attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {
        return Model::filled($model, $request_key, $model_attribute, $default);
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
