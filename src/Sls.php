<?php

namespace Zikix\Zikix;

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
    public static $logs = [];

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        self::$logs[] = $args;
        return Log::getFacadeRoot()->$method(...$args);
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    public static function put(array $data = []): void
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
                $logs[$k] = json_encode($v, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }
        }

        try {
            app('sls')->putLogs($logs);
        } catch (Exception $exception) {
            Log::error(json_encode(Common::exceptionToArray($exception), JSON_THROW_ON_ERROR));
        }
    }

    /**
     * @return array
     */
    public static function getDefaultFields(): array
    {
        try {
            $logs = Context::context();
        } catch (Exception $exception) {
            $logs = [];
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

}
