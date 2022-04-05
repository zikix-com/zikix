<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Throwable;
use function parse_str;

class Common
{
    /**
     * @return array
     */
    public static function getApiRoutes(): array
    {
        $routes = Route::getRoutes();
        $apis   = [];
        foreach ($routes as $route) {
            $middleware = $route->action['middleware'] ?? [];
            if ($middleware && in_array('api', $middleware, true)) {
                $apis[] = $route;
            }
        }

        return $apis;
    }

    /**
     * @return array
     */
    public static function getApiDoc(): array
    {
        $routes = static::getApiRoutes();
        $apis   = [];
        foreach ($routes as $route) {

            /**
             * @var $controller Controller
             */
            $class      = explode('@', $route->action['uses'])[0];
            $controller = new $class();

            $apis[] = [
                'name'        => $controller->name,
                'description' => $controller->description,
                'uri'         => $route->uri,
                'method'      => $route->methods[0],
                'where'       => $route->action['where'] ?? '',
                'rules'       => $controller->rules(),
                'attributes'  => $controller->attributes(),
            ];
        }

        return $apis;
    }

    /**
     * @return array
     */
    public static function getOpenApiDoc(): array
    {
        $routes = static::getApiRoutes();
        $apis   = [];
        foreach ($routes as $route) {

            /**
             * @var $controller Controller
             */
            $class      = explode('@', $route->action['uses'])[0];
            $controller = new $class();

            if (!property_exists($controller, 'openapi')) {
                continue;
            }

            if (!$controller->openapi) {
                continue;
            }

            $apis[] = [
                'name'        => $controller->name,
                'description' => $controller->description,
                'uri'         => $route->uri,
                'method'      => $route->methods[0],
                'where'       => $route->action['where'],
                'rules'       => $controller->rules(),
                'attributes'  => $controller->attributes(),
            ];
        }

        return $apis;
    }

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
