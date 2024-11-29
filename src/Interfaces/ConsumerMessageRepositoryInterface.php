<?php

namespace Micromus\KafkaBusRepeater\Interfaces;

use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessage;

interface ConsumerMessageRepositoryInterface
{
    public function get(): ?RepeatConsumerMessage;

    public function save(RepeatConsumerMessage $consumerMessage): void;

    public function delete(string $id): void;
}
