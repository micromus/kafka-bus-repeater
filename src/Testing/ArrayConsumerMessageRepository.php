<?php

namespace Micromus\KafkaBusRepeater\Testing;

use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessage;

class ArrayConsumerMessageRepository implements ConsumerMessageRepositoryInterface
{
    /**
     * @param RepeatConsumerMessage[] $repeatConsumerMessages
     */
    public function __construct(
        public array $repeatConsumerMessages = []
    ) {
    }

    public function get(): ?RepeatConsumerMessage
    {
        return $this->repeatConsumerMessages[0] ?? null;
    }

    public function save(RepeatConsumerMessage $consumerMessage): void
    {
        $this->repeatConsumerMessages[] = $consumerMessage;
    }

    public function delete(string $id): void
    {
        $this->repeatConsumerMessages = array_values(
            array_filter(
                $this->repeatConsumerMessages,
                fn (RepeatConsumerMessage $message) => $message->id !== $id
            )
        );
    }
}
