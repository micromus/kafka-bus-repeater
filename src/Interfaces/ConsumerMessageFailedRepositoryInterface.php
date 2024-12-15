<?php

namespace Micromus\KafkaBusRepeater\Interfaces;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;

interface ConsumerMessageFailedRepositoryInterface
{
    public function get(): ?FailedConsumerMessageInterface;

    public function save(WorkerConsumerMessageInterface $message): FailedConsumerMessageInterface;

    public function delete(string $id): void;
}
