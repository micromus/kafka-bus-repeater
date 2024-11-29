<?php

namespace Micromus\KafkaBusRepeater\Repeaters;

use Micromus\KafkaBus\Bus\Listeners\Workers\WorkerRegistry;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageHandlerFactoryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageHandlerInterface;

class RepeaterHandlers
{
    protected array $handlers = [];

    public function __construct(
        protected WorkerRegistry $workerRegistry,
        protected ConsumerMessageHandlerFactoryInterface $consumerMessageHandlerFactory
    ) {
    }

    public function getOrCreateMessageHandler(string $workerName): ConsumerMessageHandlerInterface
    {
        if (!isset($this->handlers[$workerName])) {
            $this->handlers[$workerName] = $this->createMessageHandler($workerName);
        }

        return $this->handlers[$workerName];
    }

    private function createMessageHandler(string $workerName): ConsumerMessageHandlerInterface
    {
        return $this->consumerMessageHandlerFactory
                ->create($this->workerRegistry->get($workerName));
    }
}
