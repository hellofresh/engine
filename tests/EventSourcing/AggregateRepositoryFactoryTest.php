<?php

namespace HelloFresh\Tests\Engine\EventSourcing;

use HelloFresh\Engine\EventSourcing\AggregateRepositoryFactory;
use HelloFresh\Engine\EventSourcing\AggregateRepositoryInterface;
use HelloFresh\Engine\EventStore\Adapter\InMemoryAdapter;
use HelloFresh\Engine\EventStore\EventStoreInterface;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\InMemorySnapshotAdapter;

class AggregateRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateADefaultRepository()
    {
        $factory = new AggregateRepositoryFactory();
        $repo = $factory->build();
        $this->assertInstanceOf(AggregateRepositoryInterface::class, $repo);
    }

    /**
     * @test
     */
    public function itShouldCreateARepositoryOnlyWithEventStore()
    {
        $factory = new AggregateRepositoryFactory([
            'event_store' => [
                'adapter' => InMemoryAdapter::class
            ]
        ]);
        $repo = $factory->build();
        $this->assertInstanceOf(AggregateRepositoryInterface::class, $repo);
    }

    /**
     * @test
     */
    public function itShouldCreateARepositoryWithEventStoreAndSnapshotStore()
    {
        $eventStore = $this->prophesize(EventStoreInterface::class);

        $factory = new AggregateRepositoryFactory([
            'event_store' => [
                'adapter' => InMemoryAdapter::class
            ],
            'snapshotter' => [
                'enabled' => true,
                'store' => [
                    'adapter' => InMemorySnapshotAdapter::class
                ],
                'strategy' => [
                    'arguments' => [
                        $eventStore->reveal()
                    ]
                ]
            ]
        ]);

        $repo = $factory->build();
        $this->assertInstanceOf(AggregateRepositoryInterface::class, $repo);
    }
}
