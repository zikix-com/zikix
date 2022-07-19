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
        Context::push('logs', $args);
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
        $logs = Context::get();

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

}
