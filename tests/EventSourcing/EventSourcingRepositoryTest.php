<?php

namespace HelloFresh\Tests\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\EventBus\EventBusInterface;
use HelloFresh\Engine\EventSourcing\EventSourcingRepository;
use HelloFresh\Engine\EventStore\EventStoreInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStoreInterface;
use HelloFresh\Tests\Engine\Mock\AggregateRoot;
use Prophecy\Argument;

class EventSourcingRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * @var EventBusInterface
     */
    private $eventBus;

    public function aggregateRootProvider()
    {
        return [
            [AggregateRoot::create(AggregateId::generate(), 'test1')]
        ];
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @test
     * @dataProvider aggregateRootProvider
     */
    public function itShouldSaveWithoutSnapshot(AggregateRoot $aggregateRoot)
    {
        $stream = $aggregateRoot->getEventStream();
        $this->setUpForEventStream($stream);

        $repo = new EventSourcingRepository($this->eventStore->reveal(), $this->eventBus->reveal());
        $repo->save($aggregateRoot);
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @test
     * @dataProvider aggregateRootProvider
     */
    public function itShouldSaveWithSnapshot(AggregateRoot $aggregateRoot)
    {
        $stream = $aggregateRoot->getEventStream();
        $this->setUpForEventStream($stream);

        $this->eventStore->countEventsFor($aggregateRoot->getAggregateRootId())->shouldBeCalled()->willReturn($stream->count());

        $snapshotStore = $this->prophesize(SnapshotStoreInterface::class);

        $repo = new EventSourcingRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            $snapshotStore->reveal()
        );
        $repo->save($aggregateRoot);
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @test
     * @dataProvider aggregateRootProvider
     */
    public function itShouldTakeASnapshot(AggregateRoot $aggregateRoot)
    {
        $stream = $aggregateRoot->getEventStream();
        $this->setUpForEventStream($stream);

        $version = 100;
        $this->eventStore->countEventsFor($aggregateRoot->getAggregateRootId())->shouldBeCalled()->willReturn($version);

        $snapshotStore = $this->prophesize(SnapshotStoreInterface::class);
        $snapshotStore->has($aggregateRoot->getAggregateRootId(), $version)->shouldBeCalled()->willReturn(false);
        $snapshotStore->save(Argument::type(Snapshot::class))->shouldBeCalled();

        $repo = new EventSourcingRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            $snapshotStore->reveal()
        );
        $repo->save($aggregateRoot);
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @test
     * @dataProvider aggregateRootProvider
     */
    public function itShouldLoadFromSnapshot(AggregateRoot $aggregateRoot)
    {
        $stream = $aggregateRoot->getEventStream();
        $this->eventStore = $this->prophesize(EventStoreInterface::class);
        $this->eventBus = $this->prophesize(EventBusInterface::class);
        
        $version = 100;
        $snapshot = $this->prophesize(Snapshot::class);
        $snapshot->getVersion()->shouldBeCalled()->willReturn($version);
        $snapshot->getAggregate()->shouldBeCalled()->willReturn($aggregateRoot);

        $snapshotStore = $this->prophesize(SnapshotStoreInterface::class);
        $snapshotStore->byId($aggregateRoot->getAggregateRootId())->shouldBeCalled()->willReturn($snapshot);

        $this->eventStore->fromVersion(
            $aggregateRoot->getAggregateRootId(),
            $version
        )->shouldBeCalled()->willReturn($stream);

        $repo = new EventSourcingRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            $snapshotStore->reveal()
        );
        $repo->load($aggregateRoot->getAggregateRootId(), AggregateRoot::class);
    }

    private function setUpForEventStream(EventStream $stream)
    {
        $this->eventStore = $this->prophesize(EventStoreInterface::class);
        $this->eventStore->append($stream)->shouldBeCalled();
        $this->eventBus = $this->prophesize(EventBusInterface::class);

        $stream->each(function (DomainMessage $domainMessage) {
            $this->eventBus->publish($domainMessage->getPayload())->shouldBeCalled();
        });
    }
}
