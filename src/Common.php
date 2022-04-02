<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;
use function parse_str;

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
     * @return string
     */
    public static function ip(?string $ip): string
    {
        if (!$ip) {
            return '';
        }

        if ($ip === '127.0.0.1' || $ip === '0.0.0.0') {
            return '';
        }

        return Cache::remember('location:' . $ip, 86400, static function () use ($ip) {

            try {
                $response = Http::connectTimeout(1)
                                ->timeout(1)
                                ->get("http://opendata.baidu.com/api.php?query=$ip&co=&resource_id=6006&oe=utf8");
                if (!$response->successful()) {
                    return '';
                }

                return $response['data'][0]['location'] ?? '';

            } catch (Exception $exception) {
                return '';
            }

        });
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function abbr($string)
    {
        return app('pinyin')->abbr($string);
    }

    /**
     * @param $string
     *
     * @return array|string|string[]
     */
    public static function phrase($string)
    {
        return str_replace(' ', '', app('pinyin')->phrase($string));
    }

    /**
     * @param string $uri
     * @param array $parameters
     * @param bool $appendsCurrentUri
     *
     * @return string
     */
    public static function uri(string $uri, array $parameters = [], bool $appendsCurrentUri = false): string
    {
        $uriComponents = parse_url($uri);

        if (isset($uriComponents['query'])) {
            parse_str($uriComponents['query'], $uriComponents['query']);
        } else {
            $uriComponents['query'] = [];
        }

        if ($appendsCurrentUri) {
            $newQuery = $parameters + $_GET + $uriComponents['query'];
        } else {
            $newQuery = $parameters + $uriComponents['query'];
        }

        $newUriComponents = isset($uriComponents['scheme']) ? $uriComponents['scheme'] . '://' : '';
        $newUriComponents .= $uriComponents['host'] ?? '';
        $newUriComponents .= isset($uriComponents['port']) ? ':' . $uriComponents['port'] : '';
        $newUriComponents .= $uriComponents['path'] ?? '';
        $newUriComponents .= '?' . http_build_query($newQuery);

        return $newUriComponents;
    }

}
