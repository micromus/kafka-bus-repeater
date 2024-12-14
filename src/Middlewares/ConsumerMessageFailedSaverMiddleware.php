<?php

namespace Micromus\KafkaBusRepeater\Middlewares;

use Closure;
use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMiddlewareInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageSaverInterface;
use Throwable;

final class ConsumerMessageFailedSaverMiddleware implements ConsumerMiddlewareInterface
{
    public function __construct(
        protected FailedConsumerMessageSaverInterface $failedConsumerMessageSaver,
        protected BusLoggerInterface $logger
    ) {
    }

    /**
     * @param WorkerConsumerMessageInterface $message
     * @param Closure(WorkerConsumerMessageInterface): void $next
     * @return void
     *
     * @throws Throwable
     */
    public function handle(WorkerConsumerMessageInterface $message, Closure $next): void
    {
        try {
            $next($message);
        }
        catch (Throwable $exception) {
            $failedConsumerMessage = $this->failedConsumerMessageSaver
                ->save($message, $exception);

            $this->logger
                ->error("Message #{$message->msgId()} is not handled and saved #{$failedConsumerMessage->id()}", [
                    'exception' => $exception,
                    'worker' => $message->workerName(),
                    'msg_id' => $message->msgId(),
                    'failed_id' => $failedConsumerMessage->id(),
                ]);
        }
    }
}
