<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Qy
{
    /**
     * @param           $key
     * @param Exception $e
     * @param string    $mentioned_list
     * @return void
     * @throws Exception
     */
    public static function exception($key, Exception $e, string $mentioned_list = '')
    {
        if ($e instanceof HttpResponseException) {
            return;
        }

        if ($e instanceof NotFoundHttpException) {
            return;
        }

        $data = [
            'env'        => config('app.env'),
            'Request Id' => Api::getRequestId(),
            'uri'        => request()->getUri(),
            'referer'    => request()->header('referer'),
            'ip'         => request()->getClientIp(),
            'message'    => $e->getMessage(),
            'exception'  => str_replace("\\", "\\\\", get_class($e)),
            'file'       => $e->getFile() . ':' . $e->getLine(),
            'code'       => $e->getCode(),
        ];

        Qy::markdown($key, $data, $mentioned_list);
    }

    /**
     * @param        $key
     * @param        $content
     * @param string $mentioned_list
     * @return void
     */
    public static function markdown($key, $content, string $mentioned_list = '')
    {
        $post_data = [
            'msgtype'  => 'markdown',
            'markdown' => [
                'content'        => self::getMarkdownString($content),
                'mentioned_list' => $mentioned_list,
            ],
        ];
        self::send($key, $post_data);
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
                    $markdown .= "<font color=\"comment\">$v</font>\n\n\n";
                }
            }
        }
        return $markdown;
    }


    /**
     * @param string $key
     * @param array  $post_data
     * @return void
     */
    private static function send(string $key, array $post_data)
    {
        $webhook = "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=" . $key;
        $curl    = curl_init();
        curl_setopt($curl, CURLOPT_URL, $webhook);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_exec($curl);
        curl_close($curl);
    }

}