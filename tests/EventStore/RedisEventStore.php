<?php

namespace HelloFresh\Tests\Engine\EventSourcing;

use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\Serializer\Adapter\JmsSerializerAdapter;
use HelloFresh\Engine\Serializer\Type\VectorHandler;
use HelloFresh\Engine\Serializer\Type\UuidSerializerHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use Predis\Client;
use Predis\ClientInterface;

class RedisEventStore extends EventStoreTest
{
    /**
     * @var ClientInterface
     */
    private $redis;

    public function setUp()
    {
        $this->redis = $this->setUpPredis();
        $this->eventStore = new EventStore(new RedisAdapter($this->redis, $this->setUpSerializer()));
    }

    protected function tearDown()
    {
        $this->redis->flushall();
    }

    private function setUpPredis()
    {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT') ?: "6379";

        return new Client("tcp://$host:$port");
    }

    private function setUpSerializer()
    {
        $jmsSerializer = SerializerBuilder::create()
            ->setMetadataDirs(['' => realpath(__DIR__ . '/../Mock/Config')])
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new VectorHandler());
                $registry->registerSubscribingHandler(new UuidSerializerHandler());
            })
            ->addDefaultHandlers()
            ->build();

        return new JmsSerializerAdapter($jmsSerializer);
    }
}
