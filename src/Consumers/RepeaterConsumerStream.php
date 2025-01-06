<?php

namespace Micromus\KafkaBusRepeater\Consumers;

use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerStreamInterface;

final class RepeaterConsumerStream implements RepeaterConsumerStreamInterface
{
    protected bool $forceStop = false;

    protected float $nextAt;

    public function __construct(
        protected RepeaterConsumerInterface $repeater,
        protected ConsumerMessageFailedRepositoryInterface $consumerMessageFailedRepository,
        protected $timeToSleep = 60
    ) {
        $this->nextAt = microtime(true);
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }

    public function process(bool $once = false): void
    {
        do {
            if ($this->nextAt > microtime(true)) {
                continue;
            }

            $repeatConsumerMessage = $this->consumerMessageFailedRepository
                ->get();

            if (is_null($repeatConsumerMessage)) {
                $this->nextAt += $this->timeToSleep;

                continue;
            }

            $this->repeater
                ->handle($repeatConsumerMessage);
        }
        while (!$this->forceStop && !$once);
    }
}
