<?php

namespace Micromus\KafkaBusRepeater\Testing;

use Exception;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;

class ThrowableConsumerHandler
{
    /**
     * @param ConsumerMessage $message
     * @return void
     *
     * @throws Exception
     */
    public function execute(ConsumerMessage $message): void
    {
        throw new Exception();
    }
}
