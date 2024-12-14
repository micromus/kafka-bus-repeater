<?php

namespace Micromus\KafkaBusRepeater\Testing\Repositories;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;

class ArrayConsumerMessageRepository implements ConsumerMessageRepositoryInterface
{
    protected array $commited = [];

    public function commit(ConsumerMessageInterface $message): void
    {
        $this->commited[] = $message->msgId();
    }

    public function exists(ConsumerMessageInterface $message): bool
    {
        return in_array($message->msgId(), $this->commited);
    }
}
