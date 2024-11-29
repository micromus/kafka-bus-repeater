<?php

namespace Micromus\KafkaBusRepeater\Repeaters;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageRepeatFailedException;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Repeaters\RepeaterInterface;
use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessage;
use Throwable;

class Repeater implements RepeaterInterface
{
    public function __construct(
        protected ConsumerMessageRepositoryInterface $consumerMessageRepository,
        protected RepeaterHandlers $repeaterHandlers
    ) {
    }

    public function handle(RepeatConsumerMessage $repeatConsumerMessage): void
    {
        try {
            $messageHandler = $this->repeaterHandlers
                ->getOrCreateMessageHandler($repeatConsumerMessage->workerName);

            $messageHandler->handle($repeatConsumerMessage->consumerMessage);

            $this->consumerMessageRepository
                ->delete($repeatConsumerMessage->id);
        }
        catch (Throwable $exception) {
            throw  new ConsumerMessageRepeatFailedException($repeatConsumerMessage, $exception);
        }
    }
}
