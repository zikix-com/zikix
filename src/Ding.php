<?php

namespace Zikix\Zikix;

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

class Ding
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
     * @param array $at
     *
     * @return Response|null
     * @throws Exception
     */
    public static function exception(Throwable $e, array $at = [])
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return null;
            }
        }

        $data = Common::exceptionToArray($e);

        return self::markdown($data, $at);
    }

    /**
     * @param array $content
     * @param array $at
     *
     * @return Response|null
     * @throws Exception
     */
    public static function markdown(array $content, array $at = [])
    {
        $data = [
            'app'        => config('app.name'),
            'whoami'     => exec('whoami'),
            'env'        => config('app.env'),
            'Request Id' => Context::getRequestId(),
            'uri'        => request()?->getUri(),
            'referer'    => request()?->header('referer'),
            'ip'         => request()?->getClientIp(),
            'location'   => Common::ip(request()?->ip()),
        ];

        foreach ($content as $k => $v) {
            $data[$k] = $v;
        }

        $post_data = [
            'msgtype'  => 'markdown',
            'markdown' => [
                'title' => 'app',
                'text'  => self::getMarkdownString($data),
                'at'    => $at,
            ],
        ];

        return self::send($post_data);
    }

    /**
     * @param array $array
     *
     * @return string
     */
    public static function getMarkdownString(array $array): string
    {
        $markdown = "";
        foreach ($array as $k => $v) {
            if ($v) {
                $k        = strtoupper($k);
                $markdown .= "<font color=\"warning\">$k</font>\n";

                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }

                if (strpos($v, 'http') === 0) {
                    $markdown .= "<font color=\"comment\">[$v]($v)</font>\n\n\n";
                } else {
                    $v = str_replace("\\", "\\\\", $v);

                    $markdown .= "<font color=\"comment\">$v</font>\n\n\n";
                }
            }
        }

        return $markdown;
    }


    /**
     * @param array $data
     *
     * @return Response|null
     * @throws Exception
     */
    private static function send(array $data): ?Response
    {
        $token = config('zikix.ding_token');
        if (!$token) {
            return null;
        }

        try {

            $md5 = md5(json_encode($data, JSON_THROW_ON_ERROR));
            if (!Cache::add("ding:$md5", 1, 10)) {
                return null;
            }

            return Http::post("https://oapi.dingtalk.com/robot/send?access_token=$token", $data);

        } catch (Exception $exception) {
            return null;
        }

    }


}
