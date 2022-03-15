<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static mixed channel(string $channel = null)
 * @method static LoggerInterface stack(array $channels, string $channel = null)
 *
 * @see \Illuminate\Log\Logger
 */
class Sls
{
    /**
     * @var array
     */
    public static $logs = [];

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $log = $method;
        foreach ($args as $arg) {
            $log .= ' ' . $arg;
        }

        self::$logs[] = $log;
        return Log::getFacadeRoot()->$method(...$args);
    }

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

        $sql = [];
        if ($count = count(QueryListener::$sql)) {
            $sql = [
                'count'   => $count,
                'time'    => QueryListener::$sql_time,
                'queries' => QueryListener::$sql,
            ];
        }

        try {
            app('sls')
                ->putLogs([
                              'app'      => config('app.name'),
                              'env'      => config('app.env'),
                              'request'  => json_encode(request()->toArray()),
                              'route'    => json_encode(request()->route()),
                              'response' => json_encode($data),
                              'user'     => json_encode($user),
                              'ip'       => request()->getClientIp(),
                              'headers'  => json_encode(self::getHeaders()),
                              'logs'     => json_encode(self::$logs),
                              'sql'      => json_encode($sql),
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