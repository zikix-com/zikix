<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Weixin
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected static $dontReport = [
        NotFoundHttpException::class,
        HttpResponseException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * @param Throwable $e
     *
     * @return void
     * @throws Throwable
     */
    public static function exception(Throwable $e)
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return;
            }
        }

        $data = Common::exceptionToArray($e);

        self::content('系统异常', $data);
    }


    /**
     * @param string $title
     * @param array $content
     *
     * @return void
     * @throws Exception
     */
    public static function content(string $title, array $content): void
    {
        global $argv;

        $data = [
            'app'             => config('app.name'),
            'whoami'          => exec('whoami'),
            'env'             => config('app.env'),
            'Request Id'      => Api::getRequestId(),
            'uri'             => request()?->getUri(),
            'referer'         => request()?->header('referer'),
            'ip'              => request()?->getClientIp(),
            'location'        => Common::ip(request()?->ip()),
            'argv'            => $argv ?? [],
            'request_content' => request()?->getContent() ?: '',
        ];

        foreach ($content as $k => $v) {
            $data[$k] = $v;
        }

        self::send($title, $data);
    }


    /**
     * @param string $title
     * @param array $data
     *
     * @return void
     */
    private static function send(string $title, array $data): void
    {
        $key = config('zikix.openid');
        if (!$key) {
            return;
        }

        try {
            $mds = md5(json_encode($data, JSON_THROW_ON_ERROR));
            if (!Cache::add("openid:$mds", 1, 10)) {
                return;
            }

            Http::timeout(3)
                ->post("https://weixin.sofiner.com/m?app=sofiner&openid=$key",
                       [
                           'title'   => $title,
                           'content' => $data,
                       ]);
            return;

        } catch (Exception $exception) {
            return;
        }

    }


}
