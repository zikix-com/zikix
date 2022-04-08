<?php

namespace Zikix\Zikix;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler extends Handler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Qy::exception($e);
            Weixin::exception($e);
            Sls::$exception = $e;
        });

        $this->renderable(function (Throwable $e, Request $request) {

            if ($request->ajax() || $request->acceptsJson()) {

                if ($e instanceof HttpResponseException) {
                    return $e->getResponse();
                }

                $message = '服务器繁忙';
                $data    = [];

                if ($e instanceof NotFoundHttpException) {
                    $message = "请求的API地址不存在: {$request->getMethod()} {$request->url()}";
                }

                if (!Common::isProduction()) {
                    $message           = $e->getMessage() ?: $message;
                    $data['exception'] = Common::exceptionToArray($e, true);
                }

                return Api::response(500, $message, $data, 500);
            }

            return parent::render($request, $e);

        });

    }
}
