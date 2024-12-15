<?php

namespace Micromus\KafkaBusRepeater\Testing;

use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBus\Interfaces\ResolverInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageFailedRepositoryInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;
use Micromus\KafkaBusRepeater\Messages\FailedConsumerMessageSaver;
use Micromus\KafkaBusRepeater\Middlewares\ConsumerMessageCommiterMiddleware;
use Micromus\KafkaBusRepeater\Middlewares\ConsumerMessageFailedSaverMiddleware;

class RepeaterResolver implements ResolverInterface
{
    public function __construct(
        protected ConsumerMessageFailedRepositoryInterface $consumerMessageFailedRepository,
        protected ConsumerMessageRepositoryInterface $consumerMessageRepository,
        protected BusLoggerInterface $logger
    ) {
    }

    public function resolve(string $class): mixed
    {
        return match ($class) {
            ConsumerMessageFailedSaverMiddleware::class => $this->resolveConsumerMessageFailedSaverMiddleware(),
            ConsumerMessageCommiterMiddleware::class => $this->resolveConsumerMessageCommitMiddleware(),
            default => new $class(),
        };
    }

    private function resolveConsumerMessageFailedSaverMiddleware(): ConsumerMessageFailedSaverMiddleware
    {
        return new ConsumerMessageFailedSaverMiddleware(
            new FailedConsumerMessageSaver($this->consumerMessageFailedRepository),
            $this->logger
        );
    }

    private function resolveConsumerMessageCommitMiddleware(): ConsumerMessageCommiterMiddleware
    {
        return new ConsumerMessageCommiterMiddleware(
            $this->consumerMessageRepository,
            $this->logger
        );
    }
}
