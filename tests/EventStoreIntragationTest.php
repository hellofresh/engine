<?php

namespace HelloFresh\Tests;

use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventSourcing\EventSourcingRepository;
use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\RedisSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;
use HelloFresh\Engine\Serializer\Adapter\JmsSerializerAdapter;
use HelloFresh\Engine\Serializer\Type\ArrayListHandler;
use HelloFresh\Engine\Serializer\Type\UuidSerializerHandler;
use HelloFresh\Tests\Engine\Mock\AggregateRoot;
use HelloFresh\Tests\Engine\Mock\AssignNameCommand;
use HelloFresh\Tests\Engine\Mock\AssignNameHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use Predis\Client;

class EventStoreIntragationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function isShouldCountEvents()
    {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT') ?: "6379";

        $redis = new Client("tcp://$host:$port");
        $jmsSerializer = SerializerBuilder::create()
            ->setMetadataDirs(['' => realpath(__DIR__ . '/Mock/Config')])
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new ArrayListHandler());
                $registry->registerSubscribingHandler(new UuidSerializerHandler());
            })
            ->addDefaultHandlers()
            ->build();

        $serializer = new JmsSerializerAdapter($jmsSerializer);

        $commandBus = new SimpleCommandBus();
        $eventBus = new SimpleEventBus();

        $eventStore = new EventStore(new RedisAdapter($redis, $serializer));
        $snapshotStore = new SnapshotStore(new RedisSnapshotAdapter($redis, $serializer));

        $aggregateRepo = new EventSourcingRepository($eventStore, $eventBus, $snapshotStore);

        $commandBus->subscribe(AssignNameCommand::class, new AssignNameHandler($aggregateRepo));

        $aggregateRoot = AggregateRoot::create(AggregateId::generate(), 'test1');
        $aggregateRepo->save($aggregateRoot);

        $command = new AssignNameCommand($aggregateRoot->getAggregateRootId(), 'test2');
        $commandBus->execute($command);

        $this->assertEquals(3, $eventStore->countEventsFor($aggregateRoot->getAggregateRootId()));
    }
}
