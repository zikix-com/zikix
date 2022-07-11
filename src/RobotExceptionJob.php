<?php

namespace Zikix\Zikix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Throwable $e)
    {
        $this->e = $e;

        $this->onQueue('high');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Robot::exception($this->e);
    }
}
