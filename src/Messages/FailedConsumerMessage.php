<?php

namespace Micromus\KafkaBusRepeater\Messages;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use RdKafka\Message;

final readonly class FailedConsumerMessage implements FailedConsumerMessageInterface
{
    private ConsumerMessageInterface $consumerMessage;

    public function __construct(
        public string $id,
        public string $workerName,
        public Message $message,
    ) {
        $this->consumerMessage = new ConsumerMessage($this->message);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function workerName(): string
    {
        return $this->workerName;
    }

    public function msgId(): string
    {
        return $this->consumerMessage->msgId();
    }

    public function topicName(): string
    {
        return $this->consumerMessage->topicName();
    }

    public function key(): ?string
    {
        return $this->consumerMessage->key();
    }

    public function payload(): string
    {
        return $this->consumerMessage->payload();
    }

    public function headers(): array
    {
        return $this->consumerMessage->headers();
    }

    public function original(): Message
    {
        return $this->consumerMessage->original();
    }
}
