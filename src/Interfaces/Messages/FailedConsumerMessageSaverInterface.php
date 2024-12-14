<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Messages;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;
use Throwable;

interface FailedConsumerMessageSaverInterface
{
    /**
     * @param WorkerConsumerMessageInterface $message
     * @param Throwable $exception
     * @return FailedConsumerMessageInterface
     *
     * @throws ConsumerMessageFailedException
     */
    public function save(WorkerConsumerMessageInterface $message, Throwable $exception): FailedConsumerMessageInterface;
}
