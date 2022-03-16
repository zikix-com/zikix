<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandler extends Handler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        NotFoundHttpException::class,
        HttpResponseException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
        // \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $e
     *
     * @return void
     * @throws Exception
     */
    public function report(Exception $e)
    {
        Qy::exception($e);

        Sls::$exception = $e;

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Exception $e
     *
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        $message = '服务器繁忙';
        $data    = [];

        if ($e instanceof NotFoundHttpException) {
            $message = '请求地址错误';
        }

        if ($e instanceof ModelNotFoundException) {
            $message = '指定的数据不存在：' . $e->getModel();
            if (count($e->getIds()) > 0) {
                $message .= ' ' . implode(', ', $e->getIds());
            }
            return Api::json(400, $message);
        }

        if (config('app.debug') === true) {
            $data['exception'] = Common::exceptionToArray($e, true);
        }

        return Api::json(500, $message, $data, 500);
    }

}