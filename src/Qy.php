<?php

namespace Zikix\LaravelComponent;

use Exception;

class Qy
{
    /**
     * @param Exception $e
     * @param string    $mentioned_list
     * @return void
     * @throws Exception
     */
    public static function exception(Exception $e, string $mentioned_list = '')
    {
        $data = [
            'app'        => config('app.app'),
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
            $webhook = "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=" . $key;
            $curl    = curl_init();
            curl_setopt($curl, CURLOPT_URL, $webhook);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $exception) {

        }

    }

}