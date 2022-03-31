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

}
