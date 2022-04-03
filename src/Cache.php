<?php

namespace Zikix\Zikix;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use JsonException;

class Cache
{
    /**
     * @param array $keys
     * @param int $ttl
     * @param Closure $callback
     *
     * @return mixed
     * @throws JsonException
     */
    public static function userRemember(array $keys, int $ttl, Closure $callback)
    {
        $id = Auth::id();
        return self::remember($id, $keys, $ttl, $callback);
    }

    /**
     * @param string $prefix
     * @param array $keys
     * @param int $ttl
     * @param Closure $callback
     *
     * @return mixed
     * @throws JsonException
     */
    public static function remember(string $prefix, array $keys, int $ttl, Closure $callback)
    {
        $parameters = Request::all($keys);
        foreach ($parameters as $k => $v) {
            if (!$v) {
                unset($parameters[$k]);
            }
        }

        $key = "$prefix:" . json_encode($parameters, JSON_THROW_ON_ERROR);
        return \Illuminate\Support\Facades\Cache::remember($key, $ttl, $callback);
    }
}
