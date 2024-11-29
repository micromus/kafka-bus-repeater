<?php

namespace Micromus\KafkaBusRepeater\Exceptions;

use Exception;
use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessage;
use Throwable;

final class ConsumerMessageRepeatFailedException extends Exception
{
    public function __construct(
        public readonly RepeatConsumerMessage $repeatConsumerMessage,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: "Не удалось прочитать сообщение #{$this->repeatConsumerMessage->id}",
            previous:  $previous
        );
    }
}
