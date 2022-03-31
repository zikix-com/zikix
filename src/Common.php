<?php

namespace Zikix\Component;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class Common
{
    /**
     * @param Exception $e
     * @param bool $detail
     *
     * @return array
     */
    public static function exceptionToArray(Throwable $e, bool $detail = false): array
    {
        $exception = [
            'message' => $e->getMessage(),
            'class'   => get_class($e),
            'file'    => $e->getFile() . ':' . $e->getLine(),
            'code'    => $e->getCode(),
        ];

        if ($detail) {
            $exception['previous'] = $e->getPrevious();
            $exception['trace']    = $e->getTrace();
        }

        if ($e instanceof HttpResponseException) {
            $exception['response'] = $e->getResponse();
        }

        return $exception;

    }

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $builder
     * @param string $request
     * @param array $columns
     * @param string $opt
     *
     * @return void
     */
    public static function search($builder, string $request, array $columns, string $opt = 'like'): void
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
     * @param string|null $ip
     *
     * @return string
     */
    public static function ip(?string $ip): string
    {
        if (!$ip) {
            return '';
        }

        if ($ip === '127.0.0.1' || $ip === '0.0.0.0') {
            return '';
        }

        return Cache::remember('location:' . $ip, 86400, static function () use ($ip) {

            try {
                $response = Http::connectTimeout(1)
                                ->timeout(1)
                                ->get("http://opendata.baidu.com/api.php?query=$ip&co=&resource_id=6006&oe=utf8");
                if (!$response->successful()) {
                    return '';
                }

                return $response['data'][0]['location'] ?? '';

            } catch (Exception $exception) {
                return '';
            }

        });
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function abbr($string)
    {
        return app('pinyin')->abbr($string);
    }

    /**
     * @param $string
     *
     * @return array|string|string[]
     */
    public static function phrase($string)
    {
        return str_replace(' ', '', app('pinyin')->phrase($string));
    }
}
