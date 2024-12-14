<?php

namespace Micromus\KafkaBusRepeater\Exceptions;

use Exception;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;
use Throwable;

final class ConsumerMessageFailedException extends Exception
{
    public function __construct(
        public readonly FailedConsumerMessageInterface $consumerMessage,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: "Не удалось прочитать сообщение #{$this->consumerMessage->id()}",
            previous:  $previous
        );
    }
}
