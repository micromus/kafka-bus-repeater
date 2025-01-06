<?php

namespace Micromus\KafkaBusRepeater\Consumers;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Consumers\RepeaterConsumerInterface;
use Throwable;

final class RepeaterConsumer implements RepeaterConsumerInterface
{
    public function __construct(
        protected ConsumerMessageFailedRepositoryInterface $consumerMessageRepository,
        protected RepeaterHandlers $repeaterHandlers
    ) {
    }

    public function handle(FailedConsumerMessageInterface $repeatConsumerMessage): void
    {
        try {
            $messageHandler = $this->repeaterHandlers
                ->getOrCreateMessageHandler($repeatConsumerMessage->workerName());

            $messageHandler->handle($repeatConsumerMessage);

            $this->consumerMessageRepository
                ->delete($repeatConsumerMessage->id());
        }
        catch (Throwable $exception) {
            if ($exception instanceof ConsumerMessageFailedException) {
                throw $exception;
            }

            throw new ConsumerMessageFailedException($repeatConsumerMessage, $exception);
        }
    }
}
