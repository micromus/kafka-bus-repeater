<?php

namespace Micromus\KafkaBusRepeater\Messages;

use Micromus\KafkaBus\Bus\Listeners\Workers\Worker;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageHandlerFactoryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\Messages\ConsumerMessageHandlerInterface;
use Micromus\KafkaBus\Uuid\UuidGeneratorInterface;
use Micromus\KafkaBusRepeater\Interfaces\ConsumerMessageRepositoryInterface;

class RepeatConsumerMessageHandlerFactory implements ConsumerMessageHandlerFactoryInterface
{
    public function __construct(
        protected ConsumerMessageHandlerFactoryInterface $consumerMessageHandlerFactory,
        protected ConsumerMessageRepositoryInterface $consumerMessageRepository,
        protected UuidGeneratorInterface $uuidGenerator
    ) {
    }

    public function create(Worker $worker): ConsumerMessageHandlerInterface
    {
        return new RepeatConsumerMessageHandler(
            $this->consumerMessageHandlerFactory->create($worker),
            $this->consumerMessageRepository,
            $this->uuidGenerator,
            $worker->name
        );
    }
}
