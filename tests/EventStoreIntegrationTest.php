<?php

namespace HelloFresh\Tests;

use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventSourcing\EventSourcingRepository;
use HelloFresh\Engine\EventStore\Adapter\MongoDbAdapter;
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
use MongoDB\Client as MongoClient;
use Predis\Client as RedisClient;

class EventStoreIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider eventStoreProvider
     * @param $eventStoreAdapter
     * @param $snapshotAdapter
     */
    public function isShouldStoreEvents($eventStoreAdapter, $snapshotAdapter)
    {
        $commandBus = new SimpleCommandBus();
        $eventBus = new SimpleEventBus();

        $eventStore = new EventStore($eventStoreAdapter);
        $snapshotStore = new SnapshotStore($snapshotAdapter);

        $aggregateRepo = new EventSourcingRepository($eventStore, $eventBus, $snapshotStore);

        $commandBus->subscribe(AssignNameCommand::class, new AssignNameHandler($aggregateRepo));

        $aggregateRoot = AggregateRoot::create(AggregateId::generate(), 'test1');
        $aggregateRepo->save($aggregateRoot);

        $command = new AssignNameCommand($aggregateRoot->getAggregateRootId(), 'test2');
        $commandBus->execute($command);

        $this->assertEquals(3, $eventStore->countEventsFor($aggregateRoot->getAggregateRootId()));
    }

    public function eventStoreProvider()
    {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT') ?: "6379";

        $mongoHost = getenv('MONGO_HOST');
        $mongoPort = getenv('MONGO_PORT') ?: "27017";

        $redis = new RedisClient("tcp://$host:$port");
        $mongodb = new MongoClient("mongodb://$mongoHost:$mongoPort");

        $jmsSerializer = SerializerBuilder::create()
            ->setMetadataDirs(['' => realpath(__DIR__ . '/Mock/Config')])
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new ArrayListHandler());
                $registry->registerSubscribingHandler(new UuidSerializerHandler());
            })
            ->addDefaultHandlers()
            ->build();

        $serializer = new JmsSerializerAdapter($jmsSerializer);

        return [
            [new RedisAdapter($redis, $serializer), new RedisSnapshotAdapter($redis, $serializer)],
            [new MongoDbAdapter($mongodb, 'chassis', $serializer), new RedisSnapshotAdapter($redis, $serializer)]
        ];
    }
}
