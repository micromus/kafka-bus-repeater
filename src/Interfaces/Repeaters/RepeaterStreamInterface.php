<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Repeaters;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;

interface RepeaterStreamInterface
{
    /**
     * @return void
     *
     * @throws ConsumerMessageFailedException
     */
    public function process(): void;

    public function forceStop(): void;
}
