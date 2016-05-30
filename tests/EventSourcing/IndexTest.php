<?php

namespace HelloFresh\Tests\Engine\EventSourcing;

use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventSourcing\EventSourcingRepository;
use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\RedisSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;
use HelloFresh\Engine\Serializer\Adapter\JmsSerializerAdapter;
use HelloFresh\Engine\Serializer\Type\ArrayListHandler;
use HelloFresh\Engine\Serializer\Type\UuidSerializerHandler;
use HelloFresh\Order\Application\Command\ApproveOrderCommand;
use HelloFresh\Order\Application\Command\CreateOrderCommand;
use HelloFresh\Order\Application\Handler\ApproveOrderHandler;
use HelloFresh\Order\Application\Handler\CreateOrderHandler;
use HelloFresh\Order\Domain\Customer;
use HelloFresh\Order\Domain\CustomerId;
use HelloFresh\Order\Domain\Order;
use HelloFresh\Order\Infrastructure\Persistence\Redis\RedisReadOrderRepository;
use HelloFresh\Order\Infrastructure\Persistence\Redis\WriteOrderRepository;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use Predis\Client;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testIndex()
    {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT') ?: "6379";

        $redis = new Client("tcp://$host:$port");
        $jmsSerializer = SerializerBuilder::create()
            ->setMetadataDirs(['' => realpath(__DIR__ . '/../../../src/Order/Infrastructure/Serializer/Config')])
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

        $writeRepo = new WriteOrderRepository($aggregateRepo);
        $readRepo = new RedisReadOrderRepository($redis, $aggregateRepo);

        $commandBus->subscribe(CreateOrderCommand::class, new CreateOrderHandler($writeRepo));
        $commandBus->subscribe(
            ApproveOrderCommand::class,
            new ApproveOrderHandler($writeRepo, $readRepo)
        );

        $customerId = CustomerId::generate();

        $customerId = CustomerId::fromString($customerId);
        $customer = new Customer($customerId);
        $order = Order::placeOrder($writeRepo->nextIdentity(), $customer);

        $writeRepo->add($order);

//        $command = new CreateOrderCommand(CustomerId::generate());
        $command = new ApproveOrderCommand($order->getAggregateRootId());
        $commandBus->execute($command);

        $this->assertEquals(2, $eventStore->countEventsFor($order->getAggregateRootId()));

//        dump($readRepo->byId(OrderId::fromString("75d28f45-57ba-4300-bd45-09a6f9be90cc")));
    }
}
