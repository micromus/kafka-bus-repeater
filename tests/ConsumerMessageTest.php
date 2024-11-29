<?php

use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessage;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMeta;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Messages\MessagePipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Support\Resolvers\NativeResolver;
use Micromus\KafkaBus\Testing\Connections\ConnectionFaker;
use Micromus\KafkaBus\Testing\Connections\ConnectionRegistryFaker;
use Micromus\KafkaBus\Testing\Messages\VoidConsumerHandlerFaker;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBus\Uuid\RandomUuidGenerator;
use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessage;
use Micromus\KafkaBusRepeater\Messages\RepeatConsumerMessageHandlerFactory;
use Micromus\KafkaBusRepeater\Repeaters\Repeater;
use Micromus\KafkaBusRepeater\Repeaters\RepeaterHandlers;
use Micromus\KafkaBusRepeater\Testing\ArrayConsumerMessageRepository;
use Micromus\KafkaBusRepeater\Testing\ThrowableConsumerHandler;
use RdKafka\Message;

test('can consume message', function () {
    $topicRegistry = (new TopicRegistry())
        ->add(new Topic('production.fact.products.1', 'products'));

    $connectionFaker = new ConnectionFaker($topicRegistry);

    $message = new Message();
    $message->payload = 'test-message';
    $message->headers = ['foo' => 'bar'];
    $message->partition = 5;
    $message->offset = 0;

    $connectionFaker->addMessage('products', $message);

    $workerRegistry = (new Bus\Listeners\Workers\WorkerRegistry())
        ->add(
            new Bus\Listeners\Workers\Worker(
                'default-listener',
                (new Bus\Listeners\Workers\WorkerRoutes())
                    ->add(new Bus\Listeners\Workers\Route('products', ThrowableConsumerHandler::class))
            )
        );

    $consumerMessageRepository = new ArrayConsumerMessageRepository();

    $bus = new Bus(
        new Bus\ThreadRegistry(
            new ConnectionRegistryFaker($connectionFaker),
            new Bus\Publishers\PublisherFactory(
                new ProducerStreamFactory(new MessagePipelineFactory(new NativeResolver())),
                $topicRegistry
            ),
            new Bus\Listeners\ListenerFactory(
                new ConsumerStreamFactory(
                    new RepeatConsumerMessageHandlerFactory(
                        new ConsumerMessageHandlerFactory(
                            new MessagePipelineFactory(new NativeResolver()),
                            new ConsumerRouterFactory(new NativeResolver(), $topicRegistry)
                        ),
                        $consumerMessageRepository,
                        new RandomUuidGenerator()
                    )
                ),
                $workerRegistry
            )
        ),
        'default'
    );

    $bus->listener('default-listener')
        ->listen();

    expect($connectionFaker->committedMessages)
        ->toHaveCount(1)
        ->and($connectionFaker->committedMessages['production.fact.products.1'][0])
        ->toHaveProperties([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
        ]);

    $savedConsumerMessages = $consumerMessageRepository->repeatConsumerMessages;

    expect($savedConsumerMessages)
        ->toHaveCount(1)
        ->and($savedConsumerMessages[0])
        ->toHaveProperty('workerName', 'default-listener')
        ->and($savedConsumerMessages[0]->consumerMessage)
        ->toHaveProperties([
            'payload' => 'test-message',
            'headers' => ['foo' => 'bar'],
        ]);
});

test('can repeat consume message', function () {
    $topicRegistry = (new TopicRegistry())
        ->add(new Topic('production.fact.products.1', 'products'));

    $connectionFaker = new ConnectionFaker($topicRegistry);

    $message = new Message();
    $message->payload = 'test-message';
    $message->headers = ['foo' => 'bar'];
    $message->partition = 5;
    $message->offset = 0;
    $message->topic_name = 'production.fact.products.1';

    $workerRegistry = (new Bus\Listeners\Workers\WorkerRegistry())
        ->add(
            new Bus\Listeners\Workers\Worker(
                'default-listener',
                (new Bus\Listeners\Workers\WorkerRoutes())
                    ->add(new Bus\Listeners\Workers\Route('products', VoidConsumerHandlerFaker::class))
            )
        );

    $consumerMessageRepository = new ArrayConsumerMessageRepository();

    $consumerMessageRepository->repeatConsumerMessages[] = new RepeatConsumerMessage(
        id: '800-900',
        workerName: 'default-listener',
        consumerMessage: new ConsumerMessage(
            $message->payload,
            $message->headers,
            new ConsumerMeta($message)
        )
    );

    $repeater = new Repeater(
        $consumerMessageRepository,
        new RepeaterHandlers(
            $workerRegistry,
            new ConsumerMessageHandlerFactory(
                new MessagePipelineFactory(new NativeResolver()),
                new ConsumerRouterFactory(new NativeResolver(), $topicRegistry)
            )
        )
    );

    $repeater->handle($consumerMessageRepository->get());

    expect($consumerMessageRepository->repeatConsumerMessages)
        ->toBeEmpty();
});
