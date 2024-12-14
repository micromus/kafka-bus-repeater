<?php

namespace Micromus\KafkaBusRepeater\Repeaters;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Repeaters\RepeaterInterface;
use Throwable;

class Repeater implements RepeaterInterface
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
