<?php

namespace Zikix\Zikix;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Ymlluo\GroupRobot\GroupRobot;

class Robot
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
     */
    public static function exception(Throwable $e): void
    {
        foreach (self::$dontReport as $item) {
            if ($e instanceof $item) {
                return;
            }
        }

        $data = Common::exceptionToArray($e);

        self::markdown($data);
    }

    /**
     * @param array $content
     */
    public static function markdown(array $content): void
    {
        $data = Context::context();

        foreach ($content as $k => $v) {
            $data[$k] = $v;
        }

        // https://packagist.org/packages/ymlluo/group-robot
        $robot = new GroupRobot();

        $qy_key    = config('zikix.qy_key');
        $qy_secret = config('zikix.qy_secret', '');
        if ($qy_key) {
            $robot->markdown(self::getMarkdownString($data))->cc('wechat', "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=$qy_key", $qy_secret, 'wx_1')->send();
        }

        $feishu_key    = config('zikix.feishu_key');
        $feishu_secret = config('zikix.feishu_secret', '');
        if ($feishu_key) {
            $robot->markdown(self::getFeishuMarkdownString($data))->cc('feishu', "https://open.feishu.cn/open-apis/bot/v2/hook/$feishu_key", $feishu_secret, 'feishu1')->send();
        }

    }


    /**
     * @param array $array
     *
     * @return string
     */
    public static function getFeishuMarkdownString(array $array): string
    {
        $markdown = "";
        foreach ($array as $k => $v) {
            if ($v) {
                $k        = strtoupper($k);
                $markdown .= "# $k\n";

                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }

                if (strpos($v, 'http') === 0) {
                    $markdown .= "$v\n\n\n";
                } else {
                    $v = str_replace("\\", "\\\\", $v);

                    $markdown .= "$v\n\n\n";
                }
            }
        }

        return $markdown;
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

}
