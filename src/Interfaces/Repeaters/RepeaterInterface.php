<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Repeaters;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageRepeatFailedException;
use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessage;

interface RepeaterInterface
{
    /**
     * @param RepeatConsumerMessage $repeatConsumerMessage
     * @return void
     *
     * @throws ConsumerMessageRepeatFailedException
     */
    public function handle(RepeatConsumerMessage $repeatConsumerMessage): void;
}
