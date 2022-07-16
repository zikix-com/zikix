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
            'request_id' => Api::getRequestId(),
            'app'        => config('app.name'),
            'whoami'     => exec('whoami'),
            'argv'       => $argv ?? [],
            'env'        => config('app.env'),
            'logs'       => Sls::$logs,
            'user'       => Auth::user(),
            'session'    => $_SESSION ?? [],
            'sls'        => config('zikix.sls_project') . '@' . config('zikix.sls_store'),
        ];

        // Request
        $request = request();

        if ($request?->route()) {
            $base['request']         = $request?->toArray() ?: [];
            $base['request_content'] = $request?->getContent() ?: '';
            $base['route']           = $request?->route() ?: [];
            $base['ip']              = $request?->ip() ?: '';
            $base['uri']             = $request?->getUri() ?: '';
            $base['referer']         = $request?->header('referer');
            $base['headers']         = self::getHeaders();
        }

        foreach (self::$context as $key => $value) {
            $base[$key] = $value;
        }

        return $base;
    }

    /**
     * @return array|string|null
     */
    public static function getHeaders(): array|string|null
    {
        $headers = request()?->header();
        foreach ($headers as $k => $v) {
            $headers[$k] = $v[0];
        }

        return $headers;
    }
}
