<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
     * @var Exception|null
     */
    public static $exception;

    /**
     * @var array
     */
    private static $logs = [];

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
        self::$logs[] = $args;
        return Log::getFacadeRoot()->$method(...$args);
    }

    /**
     * @param mixed $data
     * @return void
     * @throws Exception
     */
    public static function put($data = [])
    {
        $logs = self::getDefaultFields();

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_numeric($k)) {
                    break;
                }
                $logs[$k] = $v;
            }
        } else {
            $logs['data'] = $data;
        }

        foreach ($logs as $k => $v) {
            if (!is_string($v)) {
                $logs[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }

        try {
            app('sls')->putLogs($logs);
        } catch (Exception $exception) {
            Log::error(json_encode(Common::exceptionToArray($exception)));
        }
    }

    /**
     * @return array
     */
    private static function getDefaultFields(): array
    {
        try {
            $logs = [
                'request_id' => Api::getRequestId(),
                'app'        => config('app.name'),
                'whoami'     => exec('whoami'),
                'env'        => config('app.env'),
                'request'    => request()->toArray(),
                'route'      => request()->route(),
                'ip'         => request()->getClientIp(),
                'uri'        => request()->getUri(),
                'referer'    => request()->header('referer'),
                'headers'    => self::getHeaders(),
                'logs'       => self::$logs,
            ];
        } catch (Exception $exception) {
            $logs = [];
        }

        if (class_exists('\App\User') && method_exists('\App\User', 'getDefaultUser')) {
            $logs['user'] = \App\User::getDefaultUser();
        }

        if ($count = count(QueryListener::$sql)) {
            $logs['sql'] = [
                'count'   => $count,
                'time'    => QueryListener::$sql_time,
                'queries' => QueryListener::$sql,
            ];
        }

        if (self::$exception) {
            $logs['exception'] = Common::exceptionToArray(self::$exception);
        }

        return $logs;
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