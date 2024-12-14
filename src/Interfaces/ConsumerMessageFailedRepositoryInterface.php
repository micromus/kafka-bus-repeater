<?php

namespace Micromus\KafkaBusRepeater\Interfaces;

use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessage;

interface ConsumerMessageFailedRepositoryInterface
{
    public function get(): ?FailedConsumerMessage;

    public function save(FailedConsumerMessage $consumerMessage): void;

    public function delete(string $id): void;
}
