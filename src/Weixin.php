<?php

namespace Zikix\Zikix;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
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
     * @return PromiseInterface|Response|null
     */
    public static function exception(Throwable $e)
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return null;
            }
        }

        $data = Common::exceptionToArray($e);

        return self::content('系统异常', $data);
    }


    /**
     * @param string $title
     * @param array $content
     *
     * @return PromiseInterface|Response|null
     */
    public static function content(string $title, array $content = [])
    {
        $context = Context::get();

        foreach ($content as $k => $v) {
            $context[$k] = $v;
        }

        return self::send($title, $context);
    }


    /**
     * @param string $title
     * @param array $data
     *
     * @return PromiseInterface|Response|null
     */
    private static function send(string $title, array $data)
    {
        $key = config('zikix.wx_key');
        if (!$key) {
            return null;
        }

        try {
            $mds = md5(json_encode($data, JSON_THROW_ON_ERROR));
            if (!Cache::add("wx:msg:$mds", 1, 10)) {
                return null;
            }

            return Http::timeout(3)
                       ->post("https://weixin.sofiner.com/m?app=sofiner&key=$key",
                              [
                                  'title'   => $title,
                                  'content' => $data,
                              ]);

        } catch (Exception $exception) {
            return null;
        }

    }

    /**
     * @param string $token
     * @param string $app
     *
     * @return array|mixed
     */
    public static function getUser(string $token, string $app = 'sofiner'): mixed
    {
        $user = Http::get("https://weixin.sofiner.com/api/wechat/oauth/user?app=$app&token=$token");

        return $user['data'] ?? [];
    }

}
