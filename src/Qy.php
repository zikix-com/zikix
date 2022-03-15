<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param Exception $e
     * @param string    $mentioned_list
     * @return void
     * @throws Exception
     */
    public static function exception(Exception $e, string $mentioned_list = '')
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return;
            }
        }

        $data = [
            'app'        => config('app.name'),
            'env'        => config('app.env'),
            'Request Id' => Api::getRequestId(),
            'uri'        => request()->getUri(),
            'referer'    => request()->header('referer'),
            'ip'         => request()->getClientIp(),
        ];

        $items = Common::exceptionToArray($e);

        foreach ($items as $k => $v) {
            $data[$k] = $v;
        }

        Qy::markdown($data, $mentioned_list);
    }

    /**
     * @param        $content
     * @param string $mentioned_list
     * @return void
     * @throws Exception
     */
    public static function markdown($content, string $mentioned_list = '')
    {
        $post_data = [
            'msgtype'  => 'markdown',
            'markdown' => [
                'content'        => self::getMarkdownString($content),
                'mentioned_list' => $mentioned_list,
            ],
        ];

        self::send($post_data);
    }

    /**
     * @param array $array
     * @return string
     */
    public static function getMarkdownString(array $array): string
    {
        $markdown = "";
        foreach ($array as $k => $v) {
            if ($v) {
                $k        = strtoupper($k);
                $markdown .= "<font color=\"warning\">$k</font>\n";
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

            if (!Cache::add("qy:$mds", 1, 3)) {
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