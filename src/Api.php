<?php

namespace Zikix\LaravelComponent;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;

class Api
{
    /**
     * @var string
     */
    private static $requestId;

    /**
     * Success - OK
     *
     * Standard response for successful HTTP requests. The actual response will depend on the request method used. In a
     * GET request, the response will contain an entity corresponding to the requested resource. In a POST request, the
     * response will contain an entity describing or containing the result of the action.
     *
     * @param mixed  $data
     * @param string $message
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function ok($data = [], string $message = ''): JsonResponse
    {
        if ($message === '') {
            $message = __('api.ok');
        }

        return self::clientSuccess(200, $message, $data);
    }

    /**
     * Public Success Method.
     *
     * @param int    $statusCode
     * @param string $message
     * @param mixed  $data
     *
     * @return JsonResponse
     * @throws Exception
     * @deprecated
     */
    private static function clientSuccess(int $statusCode, string $message, $data = []): JsonResponse
    {
        return self::response($statusCode, $message, $data);
    }

    /**
     * @param int    $statusCode
     * @param string $message
     * @param array  $data
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     * @throws Exception
     * @deprecated
     */
    public static function response(int $statusCode, string $message, $data = [], $headers = [], $options = 0): JsonResponse
    {
        $json['request_id'] = self::getRequestId();
        $json['code']       = $statusCode;
        $json['message']    = $message;
        if ($data) {
            $json['data'] = $data;
        }

        Sls::put(['response' => $json]);

        $status = self::getCode() ? self::getCode() : $statusCode;

        return new JsonResponse($json, $status, $headers, $options);
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = Uuid::uuid4()->toString();
        }

        return strtoupper(self::$requestId);
    }

    /**
     * @return null|string|int
     */
    private static function getCode()
    {
        return config('zikix.api_http_code');
    }

    /**
     * @param int    $code
     * @param string $message
     * @param mixed  $data
     * @param int    $httpCode
     * @param array  $headers
     * @param int    $options
     * @return mixed
     * @throws Exception
     */
    public static function error(int $code, string $message = '错误', $data = [], int $httpCode = 200, array $headers = [], int $options = 0)
    {
        throw new HttpResponseException(self::json($code, $message, $data, $httpCode, $headers, $options));
    }

    /**
     * @param int    $code
     * @param string $message
     * @param mixed  $data
     * @param int    $httpCode
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     * @throws Exception
     */
    public static function json(int $code, string $message = '成功', $data = [], int $httpCode = 200, array $headers = [], int $options = 0): JsonResponse
    {
        $content['request_id'] = self::getRequestId();
        $content['code']       = $code;
        $content['message']    = $message;
        if ($data !== []) {
            $content['data'] = $data;
        }

        Sls::put(['response' => $content]);

        return new JsonResponse($content, $httpCode, $headers, $options);
    }

    /**
     * Success - Created
     *
     * The request has been fulfilled, resulting in the creation of a new resource.
     *
     * @param string $message
     * @param mixed  $data
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function created(string $message = '', $data = []): JsonResponse
    {
        if ($message === '') {
            $message = __('api.created');
        }

        return self::clientSuccess(201, $message, $data);
    }

    /**
     * Success - Accepted
     *
     * The request has been accepted for processing, but the processing has not been completed. The request might or
     * might not be eventually acted upon, and may be disallowed when processing occurs.
     *
     * @param string $message
     * @param mixed  $data
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function accepted(string $message = '', $data = []): JsonResponse
    {
        if ($message === '') {
            $message = __('api.accepted');
        }

        return self::clientSuccess(202, $message, $data);
    }

    /**
     * Success - Non-Authoritative Information
     *
     * The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is
     * returning a modified version of the origin's response.
     *
     * @param string $message
     * @param mixed  $data
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function nonAuthoritativeInformation(string $message = '', $data = []): JsonResponse
    {
        if ($message === '') {
            $message = __('api.non_authoritative');
        }

        return self::clientSuccess(203, $message, $data);
    }

    /**
     * Success - No Content
     *
     * The server successfully processed the request and is not returning any content.
     *
     * @param string $message
     * @param mixed  $data
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function noContent(string $message = '', $data = []): JsonResponse
    {
        if ($message === '') {
            $message = __('api.no_content');
        }

        return self::clientSuccess(204, $message, $data);
    }

    /**
     * Success - Reset Content
     *
     * The server successfully processed the request, but is not returning any content. Unlike a 204 response, this
     * response requires that the requester reset the document view.
     *
     * @param string $message
     * @param mixed  $data
     *
     * @return JsonResponse
     * @throws Exception
     */
    public static function resetContent(string $message = '', $data = []): JsonResponse
    {
        if ($message === '') {
            $message = __('api.reset_content');
        }

        return self::clientSuccess(205, $message, $data);
    }

    /**
     * Client errors - Bad Request
     *
     * The server cannot or will not process the request due to an apparent client error (e.g., malformed request
     * syntax, too large size, invalid request message framing, or deceptive request routing).
     *
     * @param string $message
     * @param mixed  $errors
     * @param array  $append
     * @throws Exception
     */
    public static function badRequest(string $message = '', $errors = [], array $append = []): void
    {
        if ($message === '') {
            $message = __('api.bad_request');
        }
        self::clientError(400, $message, $errors);
    }

    /**
     * @param int    $statusCode
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    private static function clientError(int $statusCode, string $message, $errors = []): void
    {
        throw new HttpResponseException(self::response($statusCode, $message, $errors));
    }

    /**
     * Client errors - Unauthorized
     *
     * Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet
     * been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to
     * the requested resource. See Basic access authentication and Digest access authentication.[32] 401 semantically
     * means "unauthenticated",[33] i.e. the user does not have the necessary credentials.
     *
     * Note: Some sites issue HTTP 401 when an IP address is banned from the website (usually the website domain) and
     * that specific address is refused permission to access a website.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function unauthorized(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.unauthorized');
        }
        self::clientError(401, $message, $errors);
    }

    /**
     * Client errors - Forbidden
     *
     * The request was valid, but the server is refusing action. The user might not have the necessary permissions for
     * a resource.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function forbidden(string $message = '', array $errors = []): void
    {
        if ($message === '') {
            $message = __('api.forbidden');
        }
        self::clientError(403, $message, $errors);
    }

    /**
     * Client errors - Not Found
     *
     * The requested resource could not be found but may be available in the future. Subsequent requests by the client
     * are permissible.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function notFound(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.not_found');
        }
        self::clientError(404, $message, $errors);
    }

    /**
     * Client errors - Method Not Allowed
     *
     * A request method is not supported for the requested resource; for example, a GET request on a form that requires
     * data to be presented via POST, or a PUT request on a read-only resource.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function methodNotAllowed(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.method_not_allowed');
        }

        self::clientError(405, $message, $errors);
    }

    /**
     * Client errors - Not Acceptable
     *
     * The requested resource is capable of generating only content not acceptable according to the Accept headers sent
     * in the request.[36] See Content negotiation.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function notAcceptable(string $message = '', $errors = null): void
    {
        if ($message === '') {
            $message = __('api.not_acceptable');
        }

        self::clientError(406, $message, $errors);
    }

    /**
     * Client errors - Conflict
     *
     * Indicates that the request could not be processed because of conflict in the request, such as an edit conflict
     * between multiple simultaneous updates.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function conflict(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.conflict');
        }

        self::clientError(409, $message, $errors);
    }

    /**
     * Client errors - Gone
     *
     * Indicates that the resource requested is no longer available and will not be available again. This should be
     * used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410
     * status code, the client should not request the resource in the future. Clients such as search engines should
     * remove the resource from their indices.[40] Most use cases do not require clients and search engines to purge
     * the resource, and a "404 Not Found" may be used instead.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function gone(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.gone');
        }

        self::clientError(410, $message, $errors);
    }

    /**
     * Client errors - Length Required
     *
     * The request did not specify the length of its content, which is required by the requested resource.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function lengthRequired(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.length_required');
        }

        self::clientError(411, $message, $errors);
    }

    /**
     * Client errors - Precondition Failed
     *
     * The server does not meet one of the preconditions that the requester put on the request.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function preconditionFailed(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.precondition_failed');
        }

        self::clientError(412, $message, $errors);
    }

    /**
     * Client errors - Unsupported Media Type
     *
     * The request entity has a media type which the server or resource does not support. For example, the client
     * uploads an image as image/svg+xml, but the server requires that images use a different format.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function unsupportedMediaType(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.unsupported_media_type');
        }

        self::clientError(413, $message, $errors);
    }

    /**
     * Client errors - Unprocessable Entity
     *
     * The request was well-formed but was unable to be followed due to semantic errors.[15].
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function unprocessableEntity(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.unprocessable_entity');
        }

        self::clientError(422, $message, $errors);
    }

    /**
     * Client errors - Precondition Required
     *
     * The origin server requires the request to be conditional. Intended to prevent "the 'lost update' problem, where
     * a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has
     * modified the state on the server, leading to a conflict.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function preconditionRequired(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.precondition_required');
        }

        self::clientError(428, $message, $errors);
    }

    /**
     * Client errors - Too Many Requests
     *
     * The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function tooManyRequests(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.too_many_requests');
        }

        self::clientError(429, $message, $errors);
    }

    /**
     * Server error - Internal Server Error
     *
     * A generic error message, given when an unexpected condition was encountered and no more specific message is
     * suitable.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function internalServerError(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.internal_server_error');
        }

        self::clientError(500, $message, $errors);
    }

    /**
     * Server error - Not Implemented
     *
     * The server either does not recognize the request method, or it lacks the ability to fulfill the request. Usually
     * this implies future availability (e.g., a new feature of a web-service API).
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function notImplemented(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.not_implemented');
        }

        self::clientError(501, $message, $errors);
    }

    /**
     * Server error - Bad Gateway
     *
     * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function badGateway(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.bad_gateway');
        }

        self::clientError(502, $message, $errors);
    }

    /**
     * Server error - Service Unavailable
     *
     * The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a
     * temporary state.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function serviceUnavailable(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.service_unavailable');
        }

        self::clientError(503, $message, $errors);
    }

    /**
     * Server error - Gateway Time-out
     *
     * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function gatewayTimeOut(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.gateway_time_out');
        }

        self::clientError(504, $message, $errors);
    }

    /**
     * Server error - HTTP Version Not Supported
     *
     * The server does not support the HTTP protocol version used in the request.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function httpVersionNotSupported(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.http_version_not_supported');
        }

        self::clientError(505, $message, $errors);
    }

    /**
     * Server error - Variant Also Negotiates
     *
     * Transparent content negotiation for the request results in a circular reference.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function variantAlsoNegotiates(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.variant_also_negotiates');
        }

        self::clientError(506, $message, $errors);
    }

    /**
     * Server error - Insufficient Storage
     *
     * The server is unable to store the representation needed to complete the request.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function insufficientStorage(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.insufficient_storage');
        }

        self::clientError(507, $message, $errors);
    }

    /**
     * Server error - Loop Detected
     *
     * The server detected an infinite loop while processing the request (sent in lieu of 208 Already Reported).
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function loopDetected(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.loop_detected');
        }

        self::clientError(508, $message, $errors);
    }

    /**
     * Server error - Not Extended
     *
     * Further extensions to the request are required for the server to fulfill it.
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function notExtended(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.not_extended');
        }

        self::clientError(510, $message, $errors);
    }

    /**
     * Server error - Network Authentication Required
     *
     * The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to
     * control access to the network (e.g., "captive portals" used to require agreement to Terms of Service before
     * granting full Internet access via a Wi-Fi hotspot).
     *
     * @param string $message
     * @param mixed  $errors
     * @throws Exception
     */
    public static function networkAuthenticationRequired(string $message = '', $errors = []): void
    {
        if ($message === '') {
            $message = __('api.network_authentication_required');
        }
        self::clientError(511, $message, $errors);
    }
}
