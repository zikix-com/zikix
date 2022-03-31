<?php

namespace Zikix\Component;

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
    public static function userSelect(array $keys, int $ttl, Closure $callback)
    {
        $id  = Auth::id();
        $key = "search:$id:" . json_encode(Request::all($keys), JSON_THROW_ON_ERROR);
        return \Illuminate\Support\Facades\Cache::remember($key, $ttl, $callback);
    }

    /**
     * @param array $keys
     * @param int $ttl
     * @param Closure $callback
     *
     * @return mixed
     * @throws JsonException
     */
    public static function allSelect(array $keys, int $ttl, Closure $callback)
    {
        $key = "search:" . json_encode(Request::all($keys), JSON_THROW_ON_ERROR);
        return \Illuminate\Support\Facades\Cache::remember($key, $ttl, $callback);
    }
}
