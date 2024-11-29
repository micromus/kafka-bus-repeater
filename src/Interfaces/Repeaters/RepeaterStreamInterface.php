<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Repeaters;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageRepeatFailedException;

interface RepeaterStreamInterface
{
    /**
     * @return void
     *
     * @throws ConsumerMessageRepeatFailedException
     */
    public function process(): void;

    public function forceStop(): void;
}
