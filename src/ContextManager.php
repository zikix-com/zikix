<?php

namespace Zikix\Zikix;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use JsonException;
use Kra8\Snowflake\Snowflake;

class ContextManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected array $base = [];

    /**
     * @var array
     */
    protected array $context = [];

    /**
     * @var string
     */
    private string $requestId;

    /**
     * Create a new Cache manager instance.
     *
     * @param Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;

        $snowflake       = app(Snowflake::class);
        $time            = date('ymdHis');
        $id              = $snowflake->next();
        $this->requestId = $time . $id;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function serialize(): string
    {
        return json_encode([
                               'requestId' => $this->requestId,
                               'base'      => $this->base,
                               'context'   => $this->context,
                           ], JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $string
     *
     * @throws JsonException
     */
    public function unserialize(string $string): void
    {
        $data = json_decode($string, true, 512, JSON_THROW_ON_ERROR);

        $this->requestId = $data['requestId'];

        $this->context = $data['context'];

        $this->base = $data['base'];
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public function set(array $context): void
    {
        $this->context = $context;
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public function append(string $key, $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * @param string $key
     * @param $item
     *
     * @return void
     */
    public function push(string $key, $item): void
    {
        if (!isset($this->context[$key])) {
            $this->context[$key] = [];
        }

        if (is_array($this->context[$key])) {
            $this->context[$key][] = $item;
        }
    }

    /**
     * @return array
     */
    public function get(): array
    {
        global $argv;

        $this->base = [
            'request_id'   => $this->getRequestId(),
            'request_time' => (microtime(true) - LARAVEL_START) * 1000, // Milliseconds
            'time'         => date('Y-m-d H:i:s'),
            'app'          => config('app.name'),
            'whoami'       => exec('whoami'),
            'argv'         => $argv ?? [],
            'env'          => config('app.env'),
            'user_id'      => Auth::id() ?: '',
            'user'         => Auth::user() ?: [],
            'session'      => $_SESSION ?? [],
            'region'       => 'cn-hangzhou',
            'sls'          => config('zikix.sls_project') . '@' . config('zikix.sls_store'),
        ];

        // Request
        $request                       = request();
        $this->base['request']         = $request?->toArray() ?: [];
        $this->base['request_length']  = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $this->base['request_content'] = $request?->getContent() ?: '';
        $this->base['method']          = $request?->getMethod() ?: '';
        $this->base['uri']             = $request?->route()?->uri() ?: [];
        $this->base['route']           = $request?->route() ?: [];
        $this->base['ip']              = $request?->ip() ?: '';
        $this->base['referer']         = $request?->header('referer');
        $this->base['headers']         = $this->getHeaders();

        // overwrite default fields
        foreach ($this->base as $key => $value) {
            $this->context[$key] = $value;
        }

        return $this->context;
    }

    /**
     * @return array|string
     */
    protected function getHeaders(): array|string
    {
        $headers = request()?->header();
        $headers = $headers ?: [];

        foreach ($headers as $k => $v) {
            $headers[$k] = $v[0];
        }

        return $headers ?: [];
    }
}
