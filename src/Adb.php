<?php

namespace Zikix\Zikix;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Adb
{
    /**
     * @param string $table
     * @param string $connection
     *
     * @return Builder
     */
    public static function table(string $table, string $connection = 'adb'): Builder
    {
        return DB::connection($connection)
                 ->table($table);
    }
}
