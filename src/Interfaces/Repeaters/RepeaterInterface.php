<?php

namespace Micromus\KafkaBusRepeater\Interfaces\Repeaters;

use Micromus\KafkaBusRepeater\Exceptions\ConsumerMessageFailedException;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessage;

interface RepeaterInterface
{
    /**
     * @param FailedConsumerMessageInterface $repeatConsumerMessage
     * @return void
     *
     * @throws ConsumerMessageFailedException
     */
    public function handle(FailedConsumerMessageInterface $repeatConsumerMessage): void;
}
