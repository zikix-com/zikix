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

class Qy
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
     * @param string $mentioned_list
     *
     * @return Response|void|null
     * @throws Exception
     */
    public static function exception(Throwable $e, string $mentioned_list = '')
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return;
            }
        }

        $data = Common::exceptionToArray($e);

        return self::markdown($data, $mentioned_list);
    }

    /**
     * @param array $content
     * @param string $mentioned_list
     *
     * @return Response|null
     * @throws Exception
     */
    public static function markdown(array $content, string $mentioned_list = '')
    {
        $data = Context::context();

        foreach ($content as $k => $v) {
            $data[$k] = $v;
        }

        $post_data = [
            'msgtype'  => 'markdown',
            'markdown' => [
                'content'        => self::getMarkdownString($data),
                'mentioned_list' => $mentioned_list,
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
        $key = config('zikix.qy_key');
        if (!$key) {
            return null;
        }

        try {
            $mds = md5(json_encode($data, JSON_THROW_ON_ERROR));
            if (!Cache::add("qy:$mds", 1, 10)) {
                return null;
            }

            return Http::post("https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=$key", $data);

        } catch (Exception $exception) {
            return null;
        }

    }

    /**
     * @param string $string
     *
     * @return Response|null
     * @throws Exception
     */
    public static function message(string $string)
    {
        return self::markdown(['message' => $string]);
    }


}
