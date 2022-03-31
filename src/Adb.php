<?php

namespace Zikix\Component;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Adb
{
    /**
     * @param string $table
     *
     * @return Builder
     */
    public static function table(string $table): Builder
    {
        return DB::connection('adb')
                 ->table($table);
    }
}
