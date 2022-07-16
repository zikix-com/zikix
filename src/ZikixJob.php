<?php

namespace Zikix\Zikix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZikixJob implements ShouldQueue
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
    protected string $requestId;

    /**
     * @var string
     */
    protected string $context;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->requestId = Api::getRequestId();

        Context::push('queue', __CLASS__);

        $this->context = json_encode(Context::context());


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
        Context::set(json_decode($this->context, true));
    }
}
