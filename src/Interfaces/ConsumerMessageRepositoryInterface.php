<?php

namespace Micromus\KafkaBusRepeater\Interfaces;

use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;

interface ConsumerMessageRepositoryInterface
{
    public function commit(ConsumerMessageInterface $message): void;

    public function exists(ConsumerMessageInterface $message): bool;
}
