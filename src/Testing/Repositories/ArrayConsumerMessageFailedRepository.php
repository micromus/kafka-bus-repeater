<?php

namespace Micromus\KafkaBusRepeater\Testing\Repositories;

use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessage;

class ArrayConsumerMessageFailedRepository implements ConsumerMessageFailedRepositoryInterface
{
    /**
     * @param FailedConsumerMessage[] $repeatConsumerMessages
     */
    public function __construct(
        public array $repeatConsumerMessages = []
    ) {
    }

    public function get(): ?FailedConsumerMessage
    {
        return $this->repeatConsumerMessages[0] ?? null;
    }

    public function save(FailedConsumerMessage $consumerMessage): void
    {
        $this->repeatConsumerMessages[] = $consumerMessage;
    }

    public function delete(string $id): void
    {
        $this->repeatConsumerMessages = array_values(
            array_filter(
                $this->repeatConsumerMessages,
                fn (FailedConsumerMessage $message) => $message->id !== $id
            )
        );
    }
}
