<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
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
        InvalidArgumentException::class,
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
            Robot::exception($e);
            Context::append('exception', Common::exceptionToArray($e));
        });

        $this->renderable(function (Throwable $e, Request $request) {

            if (Common::isApiRequest($request)) {
                return self::api($e, $request);
            }

            if (Common::isProduction() && !Common::isDebug()) {
                return self::view($e, $request);
            }

        });

    }

    /**
     * @param Throwable $e
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    private static function view(Throwable $e, Request $request): \Illuminate\Http\Response
    {
        $status     = 500;
        $message    = $e->getMessage();
        $request_id = Context::getRequestId();
        $code       = 400;

        if ($e instanceof HttpResponseException) {
            $status = $e->getResponse()->getStatusCode();

            $data = json_decode($e->getResponse()->getContent(), true);

            $message    = $data['message'] ?? '';
            $code       = $data['code'] ?? '';
            $request_id = $data['request_id'] ?? '';
        }

        return response()
            ->view('500',
                   [
                       'message'    => $message,
                       'code'       => $code,
                       'request_id' => $request_id,
                   ],
                   $status);
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

        $message = $e->getMessage() ?: '服务器繁忙';
        $data    = [];

        if (Common::isDebug()) {
            $data['exception'] = Common::exceptionToArray($e, true);
        }

        if ($e instanceof NotFoundHttpException && $e->getMessage() === '') {
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
