<?php

namespace Zikix\Zikix;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class Where
{

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $request_key
     * @param array $model_columns
     * @param string $opt
     * @param string $pre_like
     */
    public static function query($builder, string $request_key, array $model_columns, string $opt = 'like', string $pre_like = '%'): void
    {
        if (Request::filled($request_key)) {

            $value = request($request_key);

            $builder->where(function ($query) use ($model_columns, $opt, $value, $pre_like) {
                /** @var Builder $query */
                foreach ($model_columns as $column) {
                    if ($opt === 'like') {
                        if (in_array($column, ['id', 'user_id', 'phone']) && !is_numeric($value)) {
                            continue;
                        }
                        if (in_array($column, ['abbr', 'phrase']) && !ctype_alpha($value)) {
                            continue;
                        }
                        $query->orWhere($column, $opt, "$pre_like$value%");
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
