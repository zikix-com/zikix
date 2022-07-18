<?php

namespace Zikix\Zikix;

use Illuminate\Support\Facades\Auth;

class Context
{
    /**
     * @var array
     */
    protected static array $context = [];

    /**
     * @param array $context
     *
     * @return void
     */
    public static function set(array $context): void
    {
        self::$context = $context;
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public static function append(string $key, $value): void
    {
        self::$context[$key] = $value;
    }

    /**
     * @param string $key
     * @param $item
     *
     * @return void
     */
    public static function push(string $key, $item): void
    {
        if (!isset(self::$context[$key])) {
            self::$context[$key] = [];
        }

        if (is_array(self::$context[$key])) {
            self::$context[$key][] = $item;
        }
    }

    /**
     * @return array
     */
    public static function context(): array
    {
        global $argv;

        $base = [
            'request_id'   => Api::getRequestId(),
            'request_time' => microtime(true) - LARAVEL_START,
            'time'         => date('Y-m-d H:i:s'),
            'app'          => config('app.name'),
            'whoami'       => exec('whoami'),
            'argv'         => $argv ?? [],
            'env'          => config('app.env'),
            'logs'         => Sls::$logs,
            'user_id'      => Auth::id() ?: '',
            'user'         => Auth::user() ?: [],
            'session'      => $_SESSION ?? [],
            'region'       => 'cn-hangzhou',
            'sls'          => config('zikix.sls_project') . '@' . config('zikix.sls_store'),
        ];

        // Request
        $request                 = request();
        $base['request']         = $request?->toArray() ?: [];
        $base['request_length']  = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $base['request_content'] = $request?->getContent() ?: '';
        $base['method']          = $request?->getMethod() ?: '';
        $base['uri']             = $request?->route()?->uri() ?: [];
        $base['route']           = $request?->route() ?: [];
        $base['ip']              = $request?->ip() ?: '';
        $base['referer']         = $request?->header('referer');
        $base['headers']         = self::getHeaders();

        foreach ($base as $key => $value) {
            self::$context[$key] = $value;
        }

        return self::$context;
    }

    /**
     * @return array|string
     */
    public static function getHeaders(): array|string
    {
        $headers = request()?->header();
        $headers = $headers ?: [];

        foreach ($headers as $k => $v) {
            $headers[$k] = $v[0];
        }

        return $headers ?: [];
    }
}
