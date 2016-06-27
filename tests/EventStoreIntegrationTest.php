<?php

namespace HelloFresh\Tests;

use HelloFresh\Engine\CommandBus\Handler\InMemoryLocator;
use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventSourcing\AggregateRepository;
use HelloFresh\Engine\EventStore\Adapter\DbalAdapter;
use HelloFresh\Engine\EventStore\Adapter\InMemoryAdapter;
use HelloFresh\Engine\EventStore\Adapter\MongoAdapter;
use HelloFresh\Engine\EventStore\Adapter\MongoDbAdapter;
use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use HelloFresh\Engine\EventStore\Adapter\Schema\DbalSchema;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\DbalSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\InMemorySnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\RedisSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\Schema\SnapshotSchema;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;
use HelloFresh\Engine\EventStore\Snapshot\Snapshotter;
use HelloFresh\Engine\EventStore\Snapshot\Strategy\CountSnapshotStrategy;
use HelloFresh\Engine\Serializer\Adapter\JmsSerializerAdapter;
use HelloFresh\Engine\Serializer\Type\VectorHandler;
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
    private $connection;

    protected function setUp()
    {
        $connection = $this->getDoctrineConnection();
        DbalSchema::createSchema($connection);
        SnapshotSchema::createSchema($connection);
    }

    protected function tearDown()
    {
        DbalSchema::dropSchema($this->connection);
        SnapshotSchema::dropSchema($this->connection);
    }

    /**
     * @test
     * @dataProvider eventStoreProvider
     * @param $eventStoreAdapter
     * @param $snapshotAdapter
     */
    public function isShouldStoreEvents($eventStoreAdapter, $snapshotAdapter)
    {
        $locator = new InMemoryLocator();
        $commandBus = new SimpleCommandBus($locator);
        $eventBus = new SimpleEventBus();

        $eventStore = new EventStore($eventStoreAdapter);
        $snapshotStore = new SnapshotStore($snapshotAdapter);
        $snapshotter = new Snapshotter($snapshotStore, new CountSnapshotStrategy($eventStore, 5));

        $aggregateRepo = new AggregateRepository($eventStore, $eventBus, $snapshotter);

        $locator->addHandler(AssignNameCommand::class, new AssignNameHandler($aggregateRepo));

        $aggregateRoot = AggregateRoot::create(AggregateId::generate(), 'test1');
        $aggregateRepo->save($aggregateRoot);

        $command = new AssignNameCommand($aggregateRoot->getAggregateRootId(), 'test2');
        $commandBus->execute($command);
        $commandBus->execute($command);
        $commandBus->execute($command);
        $commandBus->execute($command);

        $this->assertEquals(6,
            $eventStore->countEventsFor(new StreamName('event_stream'), $aggregateRoot->getAggregateRootId()));
    }

    public function eventStoreProvider()
    {
        //Setup serializer
        $serializer = $this->configureSerializer();
        $redis = $this->configureRedis();

        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $mongodb = $this->configureMongoDB();
            $mongodbAdapter = new MongoDbAdapter($mongodb, $serializer, 'chassis');
        } else {
            $mongodb = $this->configureMongo();
            $mongodbAdapter = new MongoAdapter($mongodb, $serializer, 'chassis');
        }

        return [
            [new InMemoryAdapter(), new InMemorySnapshotAdapter()],
            [new RedisAdapter($redis, $serializer), new RedisSnapshotAdapter($redis, $serializer)],
            [$mongodbAdapter, new RedisSnapshotAdapter($redis, $serializer)],
            [
                new DbalAdapter($this->getDoctrineConnection(), $serializer, DbalSchema::TABLE_NAME),
                new DbalSnapshotAdapter($this->getDoctrineConnection(), $serializer, SnapshotSchema::TABLE_NAME)
            ]
        ];
    }

    private function configureSerializer()
    {
        $jmsSerializer = SerializerBuilder::create()
            ->setMetadataDirs(['' => realpath(__DIR__ . '/Mock/Config')])
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new VectorHandler());
                $registry->registerSubscribingHandler(new UuidSerializerHandler());
            })
            ->addDefaultHandlers()
            ->build();

        return new JmsSerializerAdapter($jmsSerializer);
    }

    private function configureMongoDB()
    {
        $host = getenv('MONGO_HOST');
        $port = getenv('MONGO_PORT') ?: "27017";

        return new MongoClient("mongodb://$host:$port");
    }

    private function configureMongo()
    {
        $host = getenv('MONGO_HOST');
        $port = getenv('MONGO_PORT') ?: "27017";

        return new \MongoClient("mongodb://$host:$port");
    }

    private function configureRedis()
    {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT') ?: "6379";

        return new RedisClient("tcp://$host:$port");
    }

    private function getDoctrineConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $connectionParams = [
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'host' => getenv('DB_HOST'),
            'driver' => 'pdo_pgsql',
        ];

        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

        return $this->connection;
    }
}
