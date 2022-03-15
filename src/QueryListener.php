<?php

namespace Zikix\LaravelComponent;

use DateTime;
use Exception;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

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
            $sql = vsprintf($sql, $event->bindings);

            self::$sql[] = [
                'sql'  => $sql . ';',
                'time' => $event->time,
            ];

            self::$sql_time += $event->time;

            (new Logger('SQL'))->pushHandler(new RotatingFileHandler(storage_path('logs/sql.log')))
                               ->info('[' . $event->time . 'ms] ' . $sql);

        } catch (Exception $exception) {
            Log::error('log sql error:' . $exception->getMessage());
        }
    }
}
