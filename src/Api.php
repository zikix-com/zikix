<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class Api
{
    /**
     * @var string
     */
    private static $requestId;

    /**
     * Success - OK
     * Standard response for successful HTTP requests. The actual response will depend on the request method used. In a
     * GET request, the response will contain an entity corresponding to the requested resource. In a POST request, the
     * response will contain an entity describing or containing the result of the action.
     *
     * @param array|object|null $data
     * @param string $message
     * @param int $bizCode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function ok(array|object|null $data = [], string $message = '', int $bizCode = 200): JsonResponse
    {
        if ($message === '') {
            $message = __('api.ok');
        }

        if (!$data) {
            $data = [];
        }

        return self::response($bizCode, $message, $data);
    }

    /**
     * @param int $bizCode
     * @param string $message
     * @param mixed $data
     * @param int $httpCode
     * @param array $headers
     * @param int $options
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function response(int $bizCode, string $message = '成功', array|object $data = [], int $httpCode = 200, array $headers = [], int $options = 0): JsonResponse
    {
        $content['request_id'] = self::getRequestId();
        $content['code']       = $bizCode;
        $content['message']    = $message;

        $env = config('app.env');

        $env_array = [
            'local'   => '开发环境',
            'test'    => '测试环境',
            'pre'     => '预发环境',
            'staging' => '预发环境',
        ];

        if (isset($env_array[$env])) {
            $content['message'] = "[$env_array[$env]] {$content['message']}";
        }

        if ($data !== []) {
            $content['data'] = $data;
        }

        // Debug users
        if (Common::isDebug()) {
            $content['console'] = Sls::getDefaultFields();
        }

        if (config('zikix.api_key_case') !== null) {
            array_change_key_case_recursive($content, config('zikix.api_key_case'));
        }

        Sls::put(['response' => $content]);

        return new JsonResponse($content, $httpCode, $headers, $options);
    }

    /**
     * @param string $requestId
     *
     * @return void
     */
    public static function setRequestId(string $requestId): void
    {
        self::$requestId = $requestId;
    }

    /**
     * @return string
     */
    public static function getRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = strtoupper(Uuid::uuid4()->toString());
        }

        return self::$requestId;
    }

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     *
     * @return Response
     * @throws Exception
     */
    public static function text(string $content = '', int $status = 200, array $headers = []): Response
    {
        Sls::put(['response_text' => $content]);

        return new Response($content, $status, $headers);
    }

    /**
     * Success - Created
     * The request has been fulfilled, resulting in the creation of a new resource.
     *
     * @param string $message
     * @param mixed $data
     * @param int $bizCode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function created(string $message = '', array|object $data = [], int $bizCode = 201): JsonResponse
    {
        if ($message === '') {
            $message = __('api.created');
        }

        return self::response($bizCode, $message, $data, 201);
    }

    /**
     * Success - Accepted
     * The request has been accepted for processing, but the processing has not been completed. The request might or
     * might not be eventually acted upon, and may be disallowed when processing occurs.
     *
     * @param string $message
     * @param mixed $data
     * @param int $bizCode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function accepted(string $message = '', array|object $data = [], int $bizCode = 202): JsonResponse
    {
        if ($message === '') {
            $message = __('api.accepted');
        }

        return self::response($bizCode, $message, $data, 202);
    }

    /**
     * Success - Non-Authoritative Information
     * The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is
     * returning a modified version of the origin's response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $bizCode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function nonAuthoritativeInformation(string $message = '', array|object $data = [], int $bizCode = 203): JsonResponse
    {
        if ($message === '') {
            $message = __('api.non_authoritative');
        }

        return self::response($bizCode, $message, $data, 203);
    }

    /**
     * Success - No Content
     * The server successfully processed the request and is not returning any content.
     *
     * @param string $message
     * @param mixed $data
     * @param int $bizCode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function noContent(string $message = '', array|object $data = [], int $bizCode = 204): JsonResponse
    {
        if ($message === '') {
            $message = __('api.no_content');
        }

        return self::response($bizCode, $message, $data, 204);
    }

    /**
     * Success - Reset Content
     * The server successfully processed the request, but is not returning any content. Unlike a 204 response, this
     * response requires that the requester reset the document view.
     *
     * @param string $message
     * @param mixed $data
     * @param int $bizCode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function resetContent(string $message = '', array|object $data = [], int $bizCode = 205): JsonResponse
    {
        if ($message === '') {
            $message = __('api.reset_content');
        }

        return self::response($bizCode, $message, $data, 205);
    }

    /**
     * Client errors - Bad Request
     * The server cannot or will not process the request due to an apparent client error (e.g., malformed request
     * syntax, too large size, invalid request message framing, or deceptive request routing).
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function badRequest(string $message = '', array|object $errors = [], int $bizCode = 400): void
    {
        if ($message === '') {
            $message = __('api.bad_request');
        }

        self::error($bizCode, $message, $errors, 400);
    }

    /**
     * @param int $bizCode
     * @param string $message
     * @param array|object $data
     * @param int $httpCode
     * @param array $headers
     * @param int $options
     *
     * @return mixed
     * @throws Exception
     */
    public static function error(int $bizCode, string $message = '错误', array|object $data = [], int $httpCode = 400, array $headers = [], int $options = 0)
    {
        // In order to take screenshots to quickly troubleshoot problems.
        // it is usually used in the management background.
        if (config('zikix.api_error_with_time')) {
            $message .= ' ' . date('Y-m-d H:i:s');
        }

        if (config('zikix.api_error_with_request_id')) {
            $message .= ' ' . self::getRequestId();
        }

        throw new HttpResponseException(self::response($bizCode, $message, $data, $httpCode, $headers, $options));
    }

    /**
     * Client errors - Unauthorized
     * Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet
     * been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to
     * the requested resource. See Basic access authentication and Digest access authentication.[32] 401 semantically
     * means "unauthenticated",[33] i.e. the user does not have the necessary credentials.
     * Note: Some sites issue HTTP 401 when an IP address is banned from the website (usually the website domain) and
     * that specific address is refused permission to access a website.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function unauthorized(string $message = '', array|object $errors = [], int $bizCode = 401): void
    {
        if ($message === '') {
            $message = __('api.unauthorized');
        }

        self::error($bizCode, $message, $errors, 401);
    }

    /**
     * Client errors - Forbidden
     * The request was valid, but the server is refusing action. The user might not have the necessary permissions for
     * a resource.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function forbidden(string $message = '', array|object $errors = [], int $bizCode = 403): void
    {
        if ($message === '') {
            $message = __('api.forbidden');
        }

        self::error($bizCode, $message, $errors, 403);
    }

    /**
     * Client errors - Not Found
     * The requested resource could not be found but may be available in the future. Subsequent requests by the client
     * are permissible.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function notFound(string $message = '', array|object $errors = [], int $bizCode = 404): void
    {
        if ($message === '') {
            $message = __('api.not_found');
        }

        self::error($bizCode, $message, $errors, 404);
    }

    /**
     * Client errors - Method Not Allowed
     * A request method is not supported for the requested resource; for example, a GET request on a form that requires
     * data to be presented via POST, or a PUT request on a read-only resource.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function methodNotAllowed(string $message = '', array|object $errors = [], int $bizCode = 405): void
    {
        if ($message === '') {
            $message = __('api.method_not_allowed');
        }

        self::error($bizCode, $message, $errors, 405);
    }

    /**
     * Client errors - Not Acceptable
     * The requested resource is capable of generating only content not acceptable according to the Accept headers sent
     * in the request.[36] See Content negotiation.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function notAcceptable(string $message = '', array|object $errors = null, int $bizCode = 406): void
    {
        if ($message === '') {
            $message = __('api.not_acceptable');
        }

        self::error($bizCode, $message, $errors, 406);
    }

    /**
     * Client errors - Conflict
     * Indicates that the request could not be processed because of conflict in the request, such as an edit conflict
     * between multiple simultaneous updates.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function conflict(string $message = '', array|object $errors = [], int $bizCode = 409): void
    {
        if ($message === '') {
            $message = __('api.conflict');
        }

        self::error($bizCode, $message, $errors, 409);
    }

    /**
     * Client errors - Gone
     * Indicates that the resource requested is no longer available and will not be available again. This should be
     * used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410
     * status code, the client should not request the resource in the future. Clients such as search engines should
     * remove the resource from their indices.[40] Most use cases do not require clients and search engines to purge
     * the resource, and a "404 Not Found" may be used instead.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function gone(string $message = '', array|object $errors = [], int $bizCode = 410): void
    {
        if ($message === '') {
            $message = __('api.gone');
        }

        self::error($bizCode, $message, $errors, 410);
    }

    /**
     * Client errors - Length Required
     * The request did not specify the length of its content, which is required by the requested resource.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function lengthRequired(string $message = '', array|object $errors = [], int $bizCode = 411): void
    {
        if ($message === '') {
            $message = __('api.length_required');
        }

        self::error($bizCode, $message, $errors, 411);
    }

    /**
     * Client errors - Precondition Failed
     * The server does not meet one of the preconditions that the requester put on the request.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function preconditionFailed(string $message = '', array|object $errors = [], int $bizCode = 412): void
    {
        if ($message === '') {
            $message = __('api.precondition_failed');
        }

        self::error($bizCode, $message, $errors, 412);
    }

    /**
     * Client errors - Unsupported Media Type
     * The request entity has a media type which the server or resource does not support. For example, the client
     * uploads an image as image/svg+xml, but the server requires that images use a different format.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function unsupportedMediaType(string $message = '', array|object $errors = [], int $bizCode = 413): void
    {
        if ($message === '') {
            $message = __('api.unsupported_media_type');
        }

        self::error($bizCode, $message, $errors, 413);
    }

    /**
     * Client errors - Unprocessable Entity
     * The request was well-formed but was unable to be followed due to semantic errors.[15].
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function unprocessableEntity(string $message = '', array|object $errors = [], int $bizCode = 422): void
    {
        if ($message === '') {
            $message = __('api.unprocessable_entity');
        }

        self::error($bizCode, $message, $errors, 422);
    }

    /**
     * Client errors - Precondition Required
     * The origin server requires the request to be conditional. Intended to prevent "the 'lost update' problem, where
     * a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has
     * modified the state on the server, leading to a conflict.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function preconditionRequired(string $message = '', array|object $errors = [], int $bizCode = 428): void
    {
        if ($message === '') {
            $message = __('api.precondition_required');
        }

        self::error($bizCode, $message, $errors, 428);
    }

    /**
     * Client errors - Too Many Requests
     * The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function tooManyRequests(string $message = '', array|object $errors = [], int $bizCode = 429): void
    {
        if ($message === '') {
            $message = __('api.too_many_requests');
        }

        self::error($bizCode, $message, $errors, 429);
    }

    /**
     * Server error - Internal Server Error
     * A generic error message, given when an unexpected condition was encountered and no more specific message is
     * suitable.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function internalServerError(string $message = '', array|object $errors = [], int $bizCode = 500): void
    {
        if ($message === '') {
            $message = __('api.internal_server_error');
        }

        self::error($bizCode, $message, $errors, 500);
    }

    /**
     * Server error - Not Implemented
     * The server either does not recognize the request method, or it lacks the ability to fulfill the request. Usually
     * this implies future availability (e.g., a new feature of a web-service API).
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function notImplemented(string $message = '', array|object $errors = [], int $bizCode = 501): void
    {
        if ($message === '') {
            $message = __('api.not_implemented');
        }

        self::error($bizCode, $message, $errors, 501);
    }

    /**
     * Server error - Bad Gateway
     * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function badGateway(string $message = '', array|object $errors = [], int $bizCode = 502): void
    {
        if ($message === '') {
            $message = __('api.bad_gateway');
        }

        self::error($bizCode, $message, $errors, 502);
    }

    /**
     * Server error - Service Unavailable
     * The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a
     * temporary state.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function serviceUnavailable(string $message = '', array|object $errors = [], int $bizCode = 503): void
    {
        if ($message === '') {
            $message = __('api.service_unavailable');
        }

        self::error($bizCode, $message, $errors, 503);
    }

    /**
     * Server error - Gateway Time-out
     * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function gatewayTimeOut(string $message = '', array|object $errors = [], int $bizCode = 504): void
    {
        if ($message === '') {
            $message = __('api.gateway_time_out');
        }

        self::error($bizCode, $message, $errors, 504);
    }

    /**
     * Server error - HTTP Version Not Supported
     * The server does not support the HTTP protocol version used in the request.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function httpVersionNotSupported(string $message = '', array|object $errors = [], int $bizCode = 505): void
    {
        if ($message === '') {
            $message = __('api.http_version_not_supported');
        }

        self::error($bizCode, $message, $errors, 505);
    }

    /**
     * Server error - Variant Also Negotiates
     * Transparent content negotiation for the request results in a circular reference.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function variantAlsoNegotiates(string $message = '', array|object $errors = [], int $bizCode = 506): void
    {
        if ($message === '') {
            $message = __('api.variant_also_negotiates');
        }

        self::error($bizCode, $message, $errors, 506);
    }

    /**
     * Server error - Insufficient Storage
     * The server is unable to store the representation needed to complete the request.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function insufficientStorage(string $message = '', array|object $errors = [], int $bizCode = 507): void
    {
        if ($message === '') {
            $message = __('api.insufficient_storage');
        }

        self::error($bizCode, $message, $errors, 507);
    }

    /**
     * Server error - Loop Detected
     * The server detected an infinite loop while processing the request (sent in lieu of 208 Already Reported).
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function loopDetected(string $message = '', array|object $errors = [], int $bizCode = 508): void
    {
        if ($message === '') {
            $message = __('api.loop_detected');
        }

        self::error($bizCode, $message, $errors, 508);
    }

    /**
     * Server error - Not Extended
     * Further extensions to the request are required for the server to fulfill it.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function notExtended(string $message = '', array|object $errors = [], int $bizCode = 510): void
    {
        if ($message === '') {
            $message = __('api.not_extended');
        }

        self::error($bizCode, $message, $errors, 510);
    }

    /**
     * Server error - Network Authentication Required
     * The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to
     * control access to the network (e.g., "captive portals" used to require agreement to Terms of Service before
     * granting full Internet access via a Wi-Fi hotspot).
     *
     * @param string $message
     * @param mixed $errors
     * @param int $bizCode
     *
     * @throws Exception
     */
    public static function networkAuthenticationRequired(string $message = '', array|object $errors = [], int $bizCode = 511): void
    {
        if ($message === '') {
            $message = __('api.network_authentication_required');
        }

        self::error($bizCode, $message, $errors, 511);
    }
}
