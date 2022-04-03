<?php

namespace Zikix\Zikix;

use Illuminate\Database\Eloquent\Builder;

class Where
{

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $key
     * @param array $columns
     * @param string $opt
     */
    public static function query($builder, string $key, array $columns, string $opt = 'like'): void
    {
        if ($value = request($key)) {

            $builder->where(function ($query) use ($columns, $opt, $value) {
                /** @var Builder $query */
                foreach ($columns as $column) {
                    if ($opt === 'like') {
                        if (in_array($column, ['id', 'user_id', 'phone']) && !is_numeric($value)) {
                            continue;
                        }
                        if (in_array($column, ['abbr', 'phrase']) && !ctype_alpha($value)) {
                            continue;
                        }
                        $query->orWhere($column, $opt, "%$value%");
                    } else {
                        $query->orWhere($column, $opt, $value);
                    }
                }
            });

        }
    }

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $column
     * @param string|null $start
     * @param string|null $end
     */
    public static function between($builder, string $column = 'created_at', string $start = null, string $end = null): void
    {
        $start = $start ?: $column . '_start';
        $end   = $end ?: $column . '_end';

        if ($startValue = request($start)) {
            $builder->where($column, '>=', $startValue);
        }

        if ($endValue = request($end)) {
            $builder->where($column, '<=', $endValue);
        }
    }

}
