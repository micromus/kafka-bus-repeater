<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Messages;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;

interface FailedConsumerMessageInterface extends WorkerConsumerMessageInterface
{
    public function id(): string;
}
