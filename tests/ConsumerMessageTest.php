<?php

use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\BusLogger;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Pipelines\PipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Testing\Connections\ConnectionFaker;
use Micromus\KafkaBus\Testing\Connections\ConnectionRegistryFaker;
use Micromus\KafkaBus\Testing\Consumers\MessageBuilder;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusRepeater\Middlewares\ConsumerMessageCommiterMiddleware;
use Micromus\KafkaBusRepeater\Middlewares\ConsumerMessageFailedSaverMiddleware;
use Micromus\KafkaBusRepeater\Repeaters\Repeater;
use Micromus\KafkaBusRepeater\Repeaters\RepeaterHandlers;
use Micromus\KafkaBusRepeater\Testing\Messages\ThrowableConsumerHandler;
use Micromus\KafkaBusRepeater\Testing\RepeaterResolver;
use Micromus\KafkaBusRepeater\Testing\Repositories\ArrayConsumerMessageFailedRepository;
use Micromus\KafkaBusRepeater\Testing\Repositories\ArrayConsumerMessageRepository;
use Psr\Log\NullLogger;

test('can consume message', function () {
    $topicRegistry = (new TopicRegistry())
        ->add(new Topic('production.fact.products.1', 'products'));

    $connectionFaker = new ConnectionFaker();

    $message = MessageBuilder::for($topicRegistry)
        ->build([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
            'topic_name' => 'products',
        ]);

    $connectionFaker->addMessage($message);

    $workerRegistry = (new Bus\Listeners\Workers\WorkerRegistry())
        ->add(
            new Bus\Listeners\Workers\Worker(
                'default-listener',
                (new Bus\Listeners\Workers\WorkerRoutes())
                    ->add(new Bus\Listeners\Workers\Route('products', ThrowableConsumerHandler::class)),
                new Bus\Listeners\Workers\Options(middlewares: [ConsumerMessageFailedSaverMiddleware::class])
            )
        );

    $consumerMessageRepository = new ArrayConsumerMessageFailedRepository();

    $resolver = new RepeaterResolver(
        $consumerMessageRepository,
        new ArrayConsumerMessageRepository(),
        new BusLogger(new NullLogger())
    );

    $bus = new Bus(
        new Bus\ThreadRegistry(
            new ConnectionRegistryFaker($connectionFaker),
            new Bus\Publishers\PublisherFactory(
                new ProducerStreamFactory(new PipelineFactory($resolver)),
                $topicRegistry
            ),
            new Bus\Listeners\ListenerFactory(
                new ConsumerStreamFactory(
                    new ConsumerMessageHandlerFactory(
                        new PipelineFactory($resolver),
                        new ConsumerRouterFactory(
                            $resolver,
                            new PipelineFactory($resolver),
                            $topicRegistry
                        )
                    ),
                ),
                $workerRegistry
            )
        ),
        'default'
    );

    $bus->createListener('default-listener')
        ->listen();

    expect($connectionFaker->committedMessages)
        ->toHaveCount(1)
        ->and($connectionFaker->committedMessages['production.fact.products.1'][0]->original())
        ->toHaveProperties([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
        ]);

    $savedConsumerMessages = $consumerMessageRepository->repeatConsumerMessages;

    expect($savedConsumerMessages)
        ->toHaveCount(1)
        ->and($savedConsumerMessages[0])
        ->toHaveProperty('workerName', 'default-listener')
        ->and($savedConsumerMessages[0]->original())
        ->toHaveProperties([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
        ]);

    $repeater = new Repeater(
        $consumerMessageRepository,
        new RepeaterHandlers(
            $workerRegistry,
            new ConsumerMessageHandlerFactory(
                new PipelineFactory($resolver),
                new ConsumerRouterFactory(
                    $resolver,
                    new PipelineFactory($resolver),
                    $topicRegistry
                )
            )
        )
    );

    $repeater->handle($consumerMessageRepository->get());

    expect($consumerMessageRepository->repeatConsumerMessages)
        ->toBeEmpty();
});

test('consume message not read if message already read', function () {
    $topicRegistry = (new TopicRegistry())
        ->add(new Topic('production.fact.products.1', 'products'));

    $connectionFaker = new ConnectionFaker();

    $message = MessageBuilder::for($topicRegistry)
        ->build([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
            'topic_name' => 'products',
        ]);

    $connectionFaker->addMessage($message);

    $workerRegistry = (new Bus\Listeners\Workers\WorkerRegistry())
        ->add(
            new Bus\Listeners\Workers\Worker(
                'default-listener',
                (new Bus\Listeners\Workers\WorkerRoutes())
                    ->add(new Bus\Listeners\Workers\Route('products', ThrowableConsumerHandler::class)),
                new Bus\Listeners\Workers\Options(middlewares: [ConsumerMessageCommiterMiddleware::class])
            )
        );

    $consumerMessageRepository = new ArrayConsumerMessageRepository();
    $consumerMessageRepository->commit(new ConsumerMessage($message));

    $resolver = new RepeaterResolver(
        new ArrayConsumerMessageFailedRepository(),
        $consumerMessageRepository,
        new BusLogger(new NullLogger())
    );

    $bus = new Bus(
        new Bus\ThreadRegistry(
            new ConnectionRegistryFaker($connectionFaker),
            new Bus\Publishers\PublisherFactory(
                new ProducerStreamFactory(new PipelineFactory($resolver)),
                $topicRegistry
            ),
            new Bus\Listeners\ListenerFactory(
                new ConsumerStreamFactory(
                    new ConsumerMessageHandlerFactory(
                        new PipelineFactory($resolver),
                        new ConsumerRouterFactory(
                            $resolver,
                            new PipelineFactory($resolver),
                            $topicRegistry
                        )
                    ),
                ),
                $workerRegistry
            )
        ),
        'default'
    );

    $bus->createListener('default-listener')
        ->listen();

    expect($connectionFaker->committedMessages)
        ->toHaveCount(1)
        ->and($connectionFaker->committedMessages['production.fact.products.1'][0]->original())
        ->toHaveProperties([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
        ]);
});
