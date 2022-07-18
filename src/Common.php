<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
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
        $routes = Route::getRoutes();
        $apis   = [];
        foreach ($routes as $route) {

            /**
             * @var $controller Controller
             */
            $class      = explode('@', $route->action['uses'])[0];
            $controller = new $class();

            if (!$controller instanceof Controller) {
                continue;
            }

            if ($controller->hidden) {
                continue;
            }

            $apis[] = [
                'service'     => $controller->service,
                'name'        => $controller->name,
                'description' => $controller->description,
                'type'        => $controller->apiType->name,
                'method'      => $route->methods[0],
                'uri'         => $route->uri,
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
        $routes = Route::getRoutes();
        $apis   = [];
        foreach ($routes as $route) {

            /**
             * @var $controller Controller
             */
            $class      = explode('@', $route->action['uses'])[0];
            $controller = new $class();

            if (!$controller instanceof Controller) {
                continue;
            }

            if ($controller->hidden) {
                continue;
            }

            if ($controller->apiType === ApiType::private) {
                continue;
            }

            $apis[] = [
                'service'     => $controller->service,
                'name'        => $controller->name,
                'description' => $controller->description,
                'type'        => $controller->apiType->name,
                'uri'         => $route->uri,
                'method'      => $route->methods[0],
                'rules'       => $controller->rules(),
                'attributes'  => $controller->attributes(),
            ];
        }

        return $apis;
    }

    /**
     * @return array
     */
    public static function getOpenApi(): array
    {
        $routes = Route::getRoutes();
        $apis   = [];
        foreach ($routes as $route) {

            /**
             * @var $controller Controller
             */
            $class      = explode('@', $route->action['uses'])[0];
            $controller = new $class();

            if (!$controller instanceof Controller) {
                continue;
            }

            if ($controller->hidden) {
                continue;
            }

            if ($controller->apiType === ApiType::private) {
                continue;
            }

            $apis[] = [
                'service'     => $controller->service,
                'name'        => $controller->name,
                'description' => $controller->description,
                'type'        => $controller->apiType->name,
                'method'      => $route->methods[0],
                'uri'         => $route->uri,
            ];
        }

        return $apis;
    }

    /**
     * @return array
     */
    public static function getApi(): array
    {
        $routes = Route::getRoutes();
        $apis   = [];
        foreach ($routes as $route) {

            /**
             * @var $controller Controller
             */
            $class      = explode('@', $route->action['uses'])[0];
            $controller = new $class();

            if (!$controller instanceof Controller) {
                continue;
            }

            if ($controller->hidden) {
                continue;
            }

            $apis[] = [
                'service'     => $controller->service,
                'name'        => $controller->name,
                'description' => $controller->description,
                'type'        => $controller->apiType->name,
                'method'      => $route->methods[0],
                'uri'         => $route->uri,
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

        if ($ip === '127.0.0.1' || $ip === '0.0.0.0' || $ip === '::1') {
            return '本地';
        }

        return Cache::remember('ip:location:' . $ip, 86400, static function () use ($ip) {

            try {
                $url = "http://opendata.baidu.com/api.php?query=$ip&co=&resource_id=6006&oe=utf8";

                $response = Http::connectTimeout(5)
                                ->timeout(5)
                                ->get($url);

                if (!$response->successful()) {
                    return '';
                }

                $ip = $response['data'][0]['location'] ?? '';

                return str_replace('-', '', $ip);

            } catch (Exception $exception) {
                return '';
            }

        });
    }

    /**
     * @param string|null $ip_whitelist
     *
     * @return bool
     * @throws Exception
     */
    public static function ipWhitelistCheck(?string $ip_whitelist): bool
    {
        $ip = \Illuminate\Support\Facades\Request::ip();

        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        if (!$ip_whitelist) {
            Api::unauthorized('Please set ip whitelist first.');
        }

        $array = explode("\n", $ip_whitelist);

        if (in_array('0.0.0.0', $array, true)) {
            return true;
        }

        if (!in_array($ip, $array, true)) {
            Api::unauthorized("Your ip $ip not in whitelist.");
        }

        return true;
    }

    /**
     * @param string|null $ip_whitelist
     * @param bool $allowPublic
     *
     * @return string
     * @throws Exception
     */
    public static function ipWhitelistFormat(?string $ip_whitelist, bool $allowPublic = false): string
    {
        if ($ip_whitelist) {
            $array = explode("\n", $ip_whitelist);

            $ip_whitelist = array_unique($array);

            if (count($ip_whitelist) > 500) {
                Api::badRequest('The number of IP addresses cannot exceed 500.');
            }

            foreach ($ip_whitelist as $k => $ip) {

                $ip = str_replace(' ', '', $ip);

                if (!$ip) {
                    unset($ip_whitelist[$k]);
                }

                if ($ip === '127.0.0.1' || $ip === '::1') {
                    Api::badRequest("Do not use $ip as the IP address.");
                }

                if (filter_var($ip, FILTER_VALIDATE_IP, [FILTER_FLAG_IPV4, FILTER_FLAG_IPV6]) === false) {
                    Api::badRequest("$ip is not a legal IP address.");
                }

            }

            if ($allowPublic === false && in_array('0.0.0.0', $ip_whitelist, true)) {
                Api::badRequest('Do not use 0.0.0.0 as the IP address.');
            }

            $ip_whitelist = implode("\n", $ip_whitelist);
        }

        return $ip_whitelist;
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

    /**
     * @return bool
     */
    public static function isProduction(): bool
    {
        return config('app.env') === 'production';
    }

    /**
     * @return bool
     */
    public static function isDebug(): bool
    {
        return config('app.debug');
    }

    /**
     * @return string
     */
    public static function dataEnv(): string
    {
        if (in_array(config('app.env'), ['production', 'pre', 'staging'])) {
            return 'production';
        }

        return 'test';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public static function isApiRequest(Request $request): bool
    {
        if ($request->is('api/*')) {
            return true;
        }

        if ($request->is('*/api/*')) {
            return true;
        }

        if ($request->ajax() || $request->isJson()) {
            return true;
        }

        if ($request->route() && in_array('api', $request->route()->middleware(), true)) {
            return true;
        }

        return false;
    }

}
