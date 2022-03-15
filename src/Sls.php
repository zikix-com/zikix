<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Sls
{
    public static $errors = [];

    /**
     * @param Exception $e
     * @param mixed     $user
     * @return void
     * @throws Exception
     */
    public static function exception(Exception $e, $user = [])
    {
        $data['request_id'] = Api::getRequestId();
        $data['code']       = 500;
        $data['message']    = $e instanceof NotFoundHttpException ? '请求地址错误' : '服务器繁忙';
        $data['exception']  = [
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'code'      => $e->getCode(),
            'message'   => $e->getMessage(),
        ];

        Sls::put($data, $user);
    }

    /**
     * @param mixed $data
     * @param mixed $user
     * @return void
     * @throws Exception
     */
    public static function put($data = [], $user = [])
    {
        if ($user === []) {
            if (class_exists('\App\User') && method_exists('\App\User', 'getDefaultUser')) {
                $user = \App\User::getDefaultUser();
            }
        }

        try {
            app('sls')->putLogs([
                                    'env'      => config('app.env'),
                                    'request'  => json_encode(request()->toArray()),
                                    'route'    => json_encode(request()->route()),
                                    'response' => json_encode($data),
                                    'user'     => json_encode($user),
                                    'ip'       => request()->getClientIp(),
                                    'headers'  => json_encode(self::getHeaders()),
                                    'errors'   => json_encode(self::$errors),
                                    'sql'      => json_encode([
                                                                  'count'   => count(QueryListener::$sql),
                                                                  'time'    => QueryListener::$sql_time,
                                                                  'queries' => QueryListener::$sql,
                                                              ]),
                                ]);
        } catch (Exception $exception) {
            Log::error(json_encode($exception));
        }
    }

    /**
     * @return array|string|null
     */
    private static function getHeaders()
    {
        $headers = request()->header();
        foreach ($headers as $k => $v) {
            $headers[$k] = $v[0];
        }

        return $headers;
    }

}