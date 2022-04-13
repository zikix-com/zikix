<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'uri'         => $route->uri,
                'service'     => $controller->service,
                'name'        => $controller->name,
                'description' => $controller->description,
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

            if (!$controller->openapi) {
                continue;
            }

            $apis[] = [
                'uri'         => $route->uri,
                'service'     => $controller->service,
                'name'        => $controller->name,
                'description' => $controller->description,
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

            if (!$controller->openapi) {
                continue;
            }

            $apis[] = [
                'uri'     => $route->uri,
                'service' => $controller->service,
                'name'    => $controller->name,
                'method'  => $route->methods[0],
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
                'service' => $controller->service,
                'name'    => $controller->name,
                'uri'     => $route->uri,
                'method'  => $route->methods[0],
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

    public static function data(): array
    {
        global $argv;

        return [
            'request_id'      => Api::getRequestId(),
            'app'             => config('app.name'),
            'whoami'          => exec('whoami'),
            'env'             => config('app.env'),
            'request'         => request()?->toArray() ?: [],
            'request_content' => request()?->getContent() ?: '',
            'route'           => request()?->route() ?: [],
            'ip'              => request()?->ip() ?: '',
            'uri'             => request()?->getUri() ?: '',
            'referer'         => request()?->header('referer'),
            'headers'         => self::getHeaders(),
            'logs'            => Sls::$logs,
            'user'            => Auth::user(),
            'argv'            => $argv ?? [],
            'session'         => $_SESSION ?? [],
            'sls'             => config('zikix.sls_project'),
        ];
    }

    /**
     * @return array|string|null
     */
    public static function getHeaders()
    {
        $headers = request()?->header();
        foreach ($headers as $k => $v) {
            $headers[$k] = $v[0];
        }

        return $headers;
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
        if (config('app.env') === 'production') {
            return 'production';
        }

        if (config('app.env') === 'pre') {
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
