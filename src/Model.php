<?php

namespace Zikix\Zikix;

use Illuminate\Support\Facades\Request;

class Model
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param string|null $attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function has(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $key,
        string                              $attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model

    {

        $attribute = $attribute ?: $key;

        if (Request::has($key)) {
            $model->setAttribute($attribute, Request::input($key, $default));
        }

        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param string|null $attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function filled(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $key,
        string                              $attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {

        $attribute = $attribute ?: $key;

        if (Request::filled($key)) {
            $model->setAttribute($attribute, Request::input($key, $default));
        }

        return $model;
    }
}
