<?php

namespace Micromus\KafkaBusRepeater\Testing\Messages;

use Exception;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;

class ThrowableConsumerHandler
{
    /**
     * @param ConsumerMessage $message
     * @return void
     *
     * @throws Exception
     */
    public function execute(ConsumerMessageInterface $message): void
    {
        // @phpstan-ignore-next-line
        if ($message instanceof FailedConsumerMessageInterface) {
            return;
        }

        throw new Exception();
    }
}
