<?php

namespace Micromus\KafkaBusRepeater\Repeaters;

use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Repeaters\RepeaterInterface;
use Micromus\KafkaBusRepeater\Interfaces\Repeaters\RepeaterStreamInterface;

class RepeaterStream implements RepeaterStreamInterface
{
    protected bool $forceStop = false;

    public function __construct(
        protected RepeaterInterface $repeater,
        protected ConsumerMessageRepositoryInterface $consumerMessageRepository,
        protected $timeToSleep = 60
    ) {
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }

    public function process(): void
    {
        do {
            $repeatConsumerMessage = $this->consumerMessageRepository
                ->get();

            if (is_null($repeatConsumerMessage)) {
                sleep($this->timeToSleep);

                continue;
            }

            $this->repeater
                ->handle($repeatConsumerMessage);
        }
        while (!$this->forceStop);
    }
}
