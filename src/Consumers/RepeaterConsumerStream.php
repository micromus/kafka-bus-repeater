<?php

namespace Micromus\KafkaBusRepeater\Consumers;

use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerStreamInterface;

class RepeaterConsumerStream implements RepeaterConsumerStreamInterface
{
    protected bool $forceStop = false;

    public function __construct(
        protected RepeaterConsumerInterface                $repeater,
        protected ConsumerMessageFailedRepositoryInterface $consumerMessageFailedRepository,
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
            $repeatConsumerMessage = $this->consumerMessageFailedRepository
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
