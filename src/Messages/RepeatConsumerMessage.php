<?php

namespace Micromus\KafkaBusRepeater\Messages;

use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;

final readonly class RepeatConsumerMessage
{
    public function __construct(
        public string $id,
        public string $workerName,
        public ConsumerMessage $consumerMessage,
    ) {
    }
}
