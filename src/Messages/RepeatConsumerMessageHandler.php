<?php

namespace Micromus\KafkaBusRepeater\Messages;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Exceptions\Consumers\MessageConsumerNotHandledException;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageHandlerInterface;
use Micromus\KafkaBus\Uuid\UuidGeneratorInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Throwable;

class RepeatConsumerMessageHandler implements ConsumerMessageHandlerInterface
{
    public function __construct(
        protected ConsumerMessageHandlerInterface $consumerMessageHandler,
        protected ConsumerMessageRepositoryInterface $consumerMessageRepository,
        protected UuidGeneratorInterface $uuidGenerator,
        protected string $workerName,
    ) {
    }

    public function topics(): array
    {
        return $this->consumerMessageHandler->topics();
    }

    public function handle(ConsumerMessage $message): void
    {
        try {
            $this->consumerMessageHandler
                ->handle($message);
        }
        catch (MessageConsumerNotHandledException $exception) {
            $this->handleException($message, $exception);
        }
    }

    protected function handleException(ConsumerMessage $message, Throwable $exception): void
    {
        $this->consumerMessageRepository
            ->save($this->mapRepeatConsumerMessage($message));
    }

    protected function mapRepeatConsumerMessage(ConsumerMessage $consumerMessage): RepeatConsumerMessage
    {
        return new RepeatConsumerMessage(
            id: $this->uuidGenerator->generate()->toString(),
            workerName: $this->workerName,
            consumerMessage: $consumerMessage
        );
    }
}
