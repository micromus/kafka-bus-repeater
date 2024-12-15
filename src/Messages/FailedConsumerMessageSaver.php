<?php

namespace Micromus\KafkaBusRepeater\Messages;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageSaverInterface;
use Throwable;

final class FailedConsumerMessageSaver implements FailedConsumerMessageSaverInterface
{
    public function __construct(
        protected ConsumerMessageFailedRepositoryInterface $consumerMessageRepository
    ) {
    }

    /**
     * @param WorkerConsumerMessageInterface $message
     * @param Throwable $exception
     * @return FailedConsumerMessageInterface
     *
     * @throws ConsumerMessageFailedException
     */
    public function save(WorkerConsumerMessageInterface $message, Throwable $exception): FailedConsumerMessageInterface
    {
        if ($message instanceof FailedConsumerMessageInterface) {
            throw new ConsumerMessageFailedException($message, $exception);
        }

        return $this->consumerMessageRepository
            ->save($message);
    }
}
