<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Response;
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

        Qy::markdown($data);
    }

    /**
     * @param string $string
     *
     * @return void
     * @throws Throwable
     */
    public static function message(string $string): void
    {
        self::content(['message' => $string]);
    }

    /**
     * @param array $content
     *
     * @return void
     * @throws Throwable
     */
    public static function content(array $content): void
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

        self::send($data);
    }


    /**
     * @param array $data
     *
     * @return Response|null
     * @throws Exception
     */
    private static function send(array $data): ?Response
    {
        $key = config('zikix.openid');
        if (!$key) {
            return null;
        }

        try {
            $mds = md5(json_encode($data, JSON_THROW_ON_ERROR));
            if (!Cache::add("openid:$mds", 1, 10)) {
                return null;
            }

            return Http::timeout(3)
                       ->post("https://weixin.sofiner.com/m?app=sofiner&openid=$key",
                              [
                                  'title'   => '11',
                                  'content' => $data,
                              ]);

        } catch (Exception $exception) {
            return null;
        }

    }


}
