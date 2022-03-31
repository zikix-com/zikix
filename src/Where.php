<?php

namespace Zikix\Component;

use Illuminate\Database\Eloquent\Builder;

class Where
{

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $request
     * @param array $columns
     * @param string $opt
     */
    public static function query($builder, string $request, array $columns, string $opt = 'like'): void
    {
        if ($keyword = request($request)) {

            $builder->where(function ($query) use ($columns, $opt, $keyword) {
                /** @var Builder $query */
                foreach ($columns as $column) {
                    if ($opt === 'like') {
                        $query->orWhere($column, $opt, "%$keyword%");
                    } else {
                        $query->orWhere($column, $opt, $keyword);
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
            $builder->where($column, '>=', $endValue);
        }
    }

}
