<?php

namespace Zikix\Zikix;

use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @method static ShouldQueue dispatch(string $message)
 */
class RobotMessageJob extends ZikixJob
{

    /**
     * @var string
     */
    protected string $message;


    /**
     * Create a new job instance.
     *
     * @param string $message
     *
     * @throws \JsonException
     */
    public function __construct(string $message)
    {
        parent::__construct();

        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        parent::handle();

        Robot::message($this->message);
    }
}
