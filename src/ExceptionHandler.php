<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
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

            if ($request->ajax() || $request->isJson()) {
                return self::api($e, $request);
            }

            if ($request->route() && in_array('api', $request->route()->middleware(), true)) {
                return self::api($e, $request);
            }

        });

    }

    /**
     * @param Throwable $e
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws Exception
     */
    private static function api(Throwable $e, Request $request)
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        $message = '服务器繁忙';
        $data    = [];

        if (!Common::isProduction()) {
            $message           = $e->getMessage() ?: $message;
            $data['exception'] = Common::exceptionToArray($e, true);
        }

        if ($e instanceof NotFoundHttpException) {
            $message = "请求的API地址不存在: {$request->getMethod()} {$request->url()}";
        }

        if ($e instanceof ModelNotFoundException) {
            $message = '指定的数据不存在：' . $e->getModel();
            if (count($e->getIds()) > 0) {
                $message .= ' ' . implode(', ', $e->getIds());
            }

            return Api::response(400, $message);
        }

        return Api::response(500, $message, $data, 500);
    }
}
