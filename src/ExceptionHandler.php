<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandler extends Handler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Illuminate\Http\Exceptions\HttpResponseException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        //        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
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
        Sls::exception($e);

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
        /**
         * 来自 Api 组件，说明已经被包装好了，可以直接 render 了
         */
        if ($e instanceof HttpResponseException) {
            return parent::render($request, $e);
        }

        /**
         * 否则就要包装统一格式的
         */
        $data['request_id'] = Api::getRequestId();
        $data['code']       = 500;
        $data['message']    = $e instanceof NotFoundHttpException ? '请求地址错误' : '服务器繁忙';
        if (config('app.env') !== 'production') {
            $data['trace'] = $e->getTrace();
        }

        return new JsonResponse($data, 500);
    }

}