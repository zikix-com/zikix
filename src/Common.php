<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class Common
{
    /**
     * @param Exception $e
     * @param bool $detail
     *
     * @return array
     */
    public static function exceptionToArray(Throwable $e, bool $detail = false): array
    {
        $exception = [
            'message' => $e->getMessage(),
            'class'   => get_class($e),
            'file'    => $e->getFile() . ':' . $e->getLine(),
            'code'    => $e->getCode(),
        ];

        if ($detail) {
            $exception['previous'] = $e->getPrevious();
            $exception['trace']    = $e->getTrace();
        }

        if ($e instanceof HttpResponseException) {
            $exception['response'] = $e->getResponse();
        }

        return $exception;

    }

    /**
     * @param string|null $ip
     *
     * @return array
     */
    public static function ip(?string $ip): array
    {
        if (!$ip) {
            return [];
        }

        $data = Cache::remember('ip:' . $ip, 86400 * 10, static function () use ($ip) {

            try {
                return Http::timeout(1)
                           ->get("http://opendata.baidu.com/api.php?query=$ip&co=&resource_id=6006&oe=utf8");
            } catch (Exception $exception) {
                return [];
            }

        });


        return $data['data'][0] ?? [];
    }
}