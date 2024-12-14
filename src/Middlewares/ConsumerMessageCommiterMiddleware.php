<?php

namespace Micromus\KafkaBusRepeater\Middlewares;

use Closure;
use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMiddlewareInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\Messages\FailedConsumerMessageInterface;

final class ConsumerMessageCommiterMiddleware implements ConsumerMiddlewareInterface
{
    public function __construct(
        protected ConsumerMessageRepositoryInterface $consumerMessageRepository,
        protected BusLoggerInterface $logger
    ) {
    }

    public function handle(WorkerConsumerMessageInterface $message, Closure $next): void
    {
        $context = [
            'worker' => $message->workerName(),
            'msg_id' => $message->msgId(),
        ];

        if ($message instanceof FailedConsumerMessageInterface) {
            $context['failed_id'] = $message->id();
        }

        if ($this->isConsumed($message)) {
            $this->logger
                ->warning("Message #{$message->msgId()} already read", $context);

            return;
        }

        $next($message);

        $this->commit($message);

        $this->logger
            ->debug("Message #{$message->msgId()} successfully read", $context);
    }

    private function isConsumed(WorkerConsumerMessageInterface $message): bool
    {
        return $this->consumerMessageRepository
            ->exists($message);
    }

    private function commit(WorkerConsumerMessageInterface $message): void
    {
        $this->consumerMessageRepository
            ->commit($message);
    }
}
