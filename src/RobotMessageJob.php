<?php

namespace Zikix\Zikix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @method static ShouldQueue dispatch(string $message)
 */
class RobotMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * @var string
     */
    protected string $message;

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
    public function __construct(string $message)
    {
        $this->message   = $message;
        $this->requestId = Api::getRequestId();
        $this->request   = Api::getRequest();

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

        Robot::message($this->message);
    }
}
