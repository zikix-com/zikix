<?php

namespace Zikix\Zikix;

use Illuminate\Support\Facades\Auth;

class Context
{
    /**
     * @var array
     */
    public static array $context = [];

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
     * @return array
     */
    public static function context(): array
    {
        global $argv;

        $base = [
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

        return array_merge(...self::$context, ...$base);
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
