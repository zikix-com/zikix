<?php

namespace Zikix\Zikix;

use DateTime;
use Exception;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Throwable;

class QueryListener
{
    /**
     * @var array SQL
     */
    public static $sql = [];

    /**
     * @var float milliseconds
     */
    public static $sql_time = 0;

    /**
     * Handle the event.
     *
     * @param QueryExecuted $event
     *
     * @return void
     */
    public function handle(QueryExecuted $event): void
    {

        try {
            if (env('APP_DEBUG') === false) {
                return;
            }

            $sql = str_replace("?", "'%s'", $event->sql);
            foreach ($event->bindings as $i => $binding) {
                if ($binding instanceof DateTime) {
                    $event->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                } else if (is_string($binding)) {
                    $event->bindings[$i] = $binding;
                }
            }

            try {
                $sql = vsprintf($sql, $event->bindings);
            } catch (Throwable $exception) {

            }

            $sql .= ';';

            self::$sql[] = [
                'sql'  => $sql,
                'time' => $event->time,
            ];

            // Alert dispatch when sql is slow query.
            $this->alertDispatch($event, $sql);

            self::$sql_time += $event->time;

            (new Logger('SQL'))->pushHandler(new RotatingFileHandler(storage_path('logs/sql.log')))
                               ->info('[' . $event->time . 'ms] ' . $sql);

        } catch (Exception $exception) {
            Log::error('log sql error:' . $exception->getMessage());
        }
    }

    /**
     * @param QueryExecuted $event
     * @param $sql
     *
     * @return void
     */
    public function alertDispatch(QueryExecuted $event, $sql): void
    {
        $connection = strtolower($event->connectionName);

        if ($connection === 'adb') {
            $configTime = config('zikix.slow_query_min_adb', 150);
        } else {
            $configTime = config('zikix.slow_query_min_mysql', 100);
        }

        // Alert dispatch when sql is slow query.
        if ($event->time > $configTime) {
            RobotMessageJob::dispatch(
                PHP_EOL
                . 'Slow Query SQL @' . $connection
                . PHP_EOL
                . $sql
                . PHP_EOL
                . PHP_EOL
                . 'Milliseconds'
                . PHP_EOL
                . $event->time
            );
        }
    }
}
