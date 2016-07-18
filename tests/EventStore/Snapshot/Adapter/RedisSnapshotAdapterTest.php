<?php

namespace HelloFresh\Tests\Engine\EventStore\Snapshot\Adapter;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\RedisSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\Serializer\SerializerInterface;
use HelloFresh\Tests\Engine\Mock\AggregateRoot;
use HelloFresh\Tests\Engine\Mock\PredisClient;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class RedisSnapshotAdapterTest extends TestCase
{
    /**
     * @var ClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $client;
    /**
     * @var SerializerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $serializer;

    protected function setUp()
    {
        $this->client = $this->prophesize(PredisClient::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
    }

    /**
     * @test
     */
    public function itCanSaveASnapshot()
    {
        $id = AggregateId::generate();
        $aggregate = AggregateRoot::create($id, 'test');

        $snapshot = Snapshot::take($id, $aggregate, '10');

        $expectedSerializedAggregate = sprintf('["serialized": "%s"]', spl_object_hash($snapshot));
        $expectedStorageArray = [
            'version' => '10',
            'created_at' => $snapshot->getCreatedAt()->format('U.u'),
            'snapshot' => [
                'type' => AggregateRoot::class,
                'payload' => $expectedSerializedAggregate,
            ]
        ];
        $expectedStoredData = '["version etc..."]';

        $this->serializer->serialize($aggregate, 'json')
            ->willReturn($expectedSerializedAggregate)
            ->shouldBeCalledTimes(1);
        $this->serializer->serialize($expectedStorageArray, 'json')
            ->willReturn($expectedStoredData)
            ->shouldBeCalledTimes(1);

        $this->client->hset(RedisSnapshotAdapter::KEY_NAMESPACE, (string)$id, $expectedStoredData)
            ->shouldBeCalledTimes(1);

        $adapter = $this->createAdapter();
        $adapter->save($snapshot);
    }

    /**
     * @test
     */
    public function aSnapshotCanBeRetrievedById()
    {
        $id = AggregateId::generate();

        $expectedAggregate = AggregateRoot::create($id, 'testing');

        $snapshotMetadata = [
            'version' => '15',
            'created_at' => '1468847497.332610',
            'snapshot' => [
                'type' => AggregateRoot::class,
                'payload' => 'aggregate_data',
            ]
        ];

        $this->mockRedisHasAndGetData($id, $snapshotMetadata);

        $this->serializer->deserialize('aggregate_data', AggregateRoot::class, 'json')
            ->willReturn($expectedAggregate);

        $adapter = $this->createAdapter();
        $result = $adapter->byId($id);

        $this->assertInstanceOf(Snapshot::class, $result);
        $this->assertSame($id, $result->getAggregateId());
        $this->assertSame($expectedAggregate, $result->getAggregate());
        $this->assertSame('15', $result->getVersion());
        $this->assertSame('1468847497.332610', $result->getCreatedAt()->format('U.u'));
        $this->assertEquals(new \DateTimeZone('UTC'), $result->getCreatedAt()->getTimezone());
    }

    /**
     * @test
     */
    public function aSnapshotCanNotBeRetrievedWhenTheIdIsUnknown()
    {
        $id = AggregateId::generate();

        $this->client->hexists(RedisSnapshotAdapter::KEY_NAMESPACE, (string)$id)
            ->willReturn(false)
            ->shouldBeCalledTimes(1);

        $adapter = $this->createAdapter();
        $result = $adapter->byId($id);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function itIndicatedIfASnapshotOfAggregateWithVersionExists()
    {
        $id = AggregateId::generate();
        $expectedDeserializedRedisData = ['version' => 20];

        $this->mockRedisHasAndGetData($id, $expectedDeserializedRedisData);

        $adapter = $this->createAdapter();
        $result = $adapter->has($id, 20);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function itIndicatedThatASnapshotOfAggregateIsUnknown()
    {
        $id = AggregateId::generate();

        $this->client->hexists(RedisSnapshotAdapter::KEY_NAMESPACE, (string)$id)
            ->willReturn(false)
            ->shouldBeCalledTimes(1);

        $adapter = $this->createAdapter();
        $result = $adapter->has($id, 15);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function itIndicatedThatASnapshotOfAggregateIsUnknownWhenTheVersionIsIncorrect()
    {
        $id = AggregateId::generate();

        $this->mockRedisHasAndGetData($id, 20);

        $adapter = $this->createAdapter();
        $result = $adapter->has($id, 15);

        $this->assertFalse($result);
    }


    /**
     * @return RedisSnapshotAdapter
     */
    protected function createAdapter()
    {
        $adapter = new RedisSnapshotAdapter($this->client->reveal(), $this->serializer->reveal());
        return $adapter;
    }

    /**
     * @param $id
     * @param $expectedDeserializedRedisData
     */
    protected function mockRedisHasAndGetData($id, $expectedDeserializedRedisData)
    {
        $this->client->hexists(RedisSnapshotAdapter::KEY_NAMESPACE, (string)$id)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->client->hget(RedisSnapshotAdapter::KEY_NAMESPACE, (string)$id)
            ->willReturn('redis_data')
            ->shouldBeCalledTimes(1);

        $this->serializer->deserialize('redis_data', 'array', 'json')
            ->willReturn($expectedDeserializedRedisData);
    }
}
