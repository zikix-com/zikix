<?php

namespace Zikix\Zikix;

use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

/**
 * @method static ShouldQueue dispatch(Throwable $e)
 */
class RobotExceptionJob extends ZikixJob
{
    /**
     * @var Throwable
     */
    protected Throwable $e;

    /**
     * Create a new job instance.
     *
     * @param Throwable $e
     *
     * @throws \JsonException
     */
    public function __construct(Throwable $e)
    {
        parent::__construct();

        $this->e = $e;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        parent::handle();

        Robot::exception($this->e);
    }
}
