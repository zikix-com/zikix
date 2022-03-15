<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;

class Common
{
    /**
     * @param Exception $e
     * @param bool      $detail
     * @return array
     */
    public static function exceptionToArray(Exception $e, bool $detail = false): array
    {
        $exception = [
            'message' => $e->getMessage(),
            'class'   => get_class($e),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
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
}