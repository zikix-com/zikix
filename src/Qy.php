<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
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
     * @return void
     * @throws Throwable
     */
    public static function exception(Throwable $e, string $mentioned_list = '')
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return;
            }
        }

        $data = Common::exceptionToArray($e);

        Qy::markdown($data, $mentioned_list);
    }

    /**
     * @param array $content
     * @param string $mentioned_list
     *
     * @return void
     * @throws Throwable
     */
    public static function markdown(array $content, string $mentioned_list = ''): void
    {
        $data = [
            'app'        => config('app.name'),
            'whoami'     => exec('whoami'),
            'env'        => config('app.env'),
            'Request Id' => Api::getRequestId(),
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
                'content'        => self::getMarkdownString($data),
                'mentioned_list' => $mentioned_list,
            ],
        ];

        self::send($post_data);
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
     * @param array $post_data
     *
     * @return void
     * @throws Exception
     */
    private static function send(array $post_data)
    {
        $key = config('zikix.qy_key');
        if (!$key) {
            return;
        }

        try {

            $post_data = json_encode($post_data);
            $mds       = md5($post_data);

            if (!Cache::add("qy:$mds", 1, 10)) {
                return;
            }

            $webhook = "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=" . $key;
            $curl    = curl_init();
            curl_setopt($curl, CURLOPT_URL, $webhook);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $exception) {

        }

    }

}