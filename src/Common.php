<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;

class Common
{
    /**
     * @param Exception $e
     * @return array
     */
    public static function exceptionToArray(Exception $e): array
    {
        $exception = [
            'message'  => $e->getMessage(),
            'class'    => get_class($e),
            'file'     => $e->getFile(),
            'line'     => $e->getLine(),
            'code'     => $e->getCode(),
            'previous' => $e->getPrevious(),
            'trace'    => $e->getTrace(),
        ];

        if ($e instanceof HttpResponseException) {
            $exception['response'] = $e->getResponse();
        }

        return $exception;

    }
}