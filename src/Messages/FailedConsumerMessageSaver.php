<?php

namespace Micromus\KafkaBusRepeater\Messages;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBus\Uuid\UuidGeneratorInterface;
use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageSaverInterface;
use Throwable;

final class FailedConsumerMessageSaver implements FailedConsumerMessageSaverInterface
{
    public function __construct(
        protected ConsumerMessageFailedRepositoryInterface $consumerMessageRepository,
        protected UuidGeneratorInterface $uuidGenerator
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

        $failedConsumerMessage = $this->mapFailedConsumerMessage($message);

        $this->consumerMessageRepository
            ->save($failedConsumerMessage);

        return $failedConsumerMessage;
    }

    protected function mapFailedConsumerMessage(WorkerConsumerMessageInterface $consumerMessage): FailedConsumerMessage
    {
        return new FailedConsumerMessage(
            id: $this->uuidGenerator->generate()->toString(),
            workerName: $consumerMessage->workerName(),
            message: $consumerMessage->original(),
        );
    }
}
