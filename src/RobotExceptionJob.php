<?php

namespace Zikix\Zikix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * @method static ShouldQueue dispatch(Throwable $e)
 */
class RobotExceptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * @var Throwable
     */
    protected Throwable $e;

    /**
     * @var string
     */
    protected string $requestId;

    /**
     * @var mixed|Request|string|array|null
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Throwable $e)
    {
        $this->e         = $e;
        $this->requestId = Api::getRequestId();
        $this->request   = \request();

        $this->onQueue('high');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Api::setRequestId($this->requestId);
        Api::setRequest($this->request);

        Robot::exception($this->e);
    }
}
