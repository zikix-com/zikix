<?php

namespace Zikix\Zikix;

use Illuminate\Support\Facades\Request;

class Model
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request_key
     * @param string|null $model_attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function has(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request_key,
        string                              $model_attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model

    {

        $model_attribute = $model_attribute ?: $request_key;

        if (Request::has($request_key)) {
            $model->setAttribute($model_attribute, Request::input($request_key, $default));
        }

        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $request_key
     * @param string|null $model_attribute
     * @param null $default
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function filled(
        \Illuminate\Database\Eloquent\Model $model,
        string                              $request_key,
        string                              $model_attribute = null,
                                            $default = null
    ): \Illuminate\Database\Eloquent\Model
    {

        $model_attribute = $model_attribute ?: $request_key;

        if (Request::filled($request_key)) {
            $model->setAttribute($model_attribute, Request::input($request_key, $default));
        }

        return $model;
    }
}
