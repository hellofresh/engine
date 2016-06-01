<?php

namespace HelloFresh\Tests;

use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventSourcing\AggregateRepository;
use HelloFresh\Engine\EventStore\Adapter\MongoAdapter;
use HelloFresh\Engine\EventStore\Adapter\MongoDbAdapter;
use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\RedisSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;
use HelloFresh\Engine\EventStore\Snapshot\Snapshotter;
use HelloFresh\Engine\EventStore\Snapshot\Strategy\CountSnapshotStrategy;
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

/**
 * @group integration
 */
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
        $snapshotter = new Snapshotter($snapshotStore, new CountSnapshotStrategy($eventStore));

        $aggregateRepo = new AggregateRepository($eventStore, $eventBus, $snapshotter);

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

        //Setup serializer
        $jmsSerializer = SerializerBuilder::create()
            ->setMetadataDirs(['' => realpath(__DIR__ . '/Mock/Config')])
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new ArrayListHandler());
                $registry->registerSubscribingHandler(new UuidSerializerHandler());
            })
            ->addDefaultHandlers()
            ->build();
        $serializer = new JmsSerializerAdapter($jmsSerializer);

        $redis = new RedisClient("tcp://$host:$port");

        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $mongodb = new MongoClient("mongodb://$mongoHost:$mongoPort");
            $mongodbAdapter = new MongoDbAdapter($mongodb, 'chassis', $serializer);
        } else {
            $mongodb = new \MongoClient("mongodb://$mongoHost:$mongoPort");
            $mongodbAdapter = new MongoAdapter($mongodb, 'chassis', $serializer);
        }

        return [
            [new RedisAdapter($redis, $serializer), new RedisSnapshotAdapter($redis, $serializer)],
            [$mongodbAdapter, new RedisSnapshotAdapter($redis, $serializer)]
        ];
    }
}
