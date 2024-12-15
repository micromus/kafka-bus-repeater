<?php

namespace Micromus\KafkaBusRepeater\Testing\Repositories;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessage;

class ArrayConsumerMessageFailedRepository implements ConsumerMessageFailedRepositoryInterface
{
    private int $id = 1;
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

    public function save(WorkerConsumerMessageInterface $message): FailedConsumerMessageInterface
    {
        $failedConsumerMessage = new FailedConsumerMessage(
            id: (string) $this->id++,
            workerName: $message->workerName(),
            message: $message->original()
        );

        $this->repeatConsumerMessages[] = $failedConsumerMessage;

        return $failedConsumerMessage;
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
