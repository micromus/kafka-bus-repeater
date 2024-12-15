<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Consumers;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;

interface RepeaterConsumerStreamInterface
{
    /**
     * @return void
     *
     * @throws ConsumerMessageFailedException
     */
    public function process(): void;

    public function forceStop(): void;
}
