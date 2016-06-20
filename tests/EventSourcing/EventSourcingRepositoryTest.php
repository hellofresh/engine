<?php

namespace HelloFresh\Tests\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\EventBus\EventBusInterface;
use HelloFresh\Engine\EventSourcing\AggregateRepository;
use HelloFresh\Engine\EventStore\EventStoreInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStoreInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshotter;
use HelloFresh\Engine\EventStore\Snapshot\Strategy\SnapshotStrategyInterface;
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

        $repo = new AggregateRepository($this->eventStore->reveal(), $this->eventBus->reveal());
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

        $snapshotStore = $this->prophesize(SnapshotStoreInterface::class);

        $strategy = $this->prophesize(SnapshotStrategyInterface::class);
        $strategy->isFulfilled(new StreamName('event_stream'), $aggregateRoot)->shouldBeCalled()->willReturn(true);
        $snapshotter = new Snapshotter($snapshotStore->reveal(), $strategy->reveal());

        $repo = new AggregateRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            $snapshotter
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

        $snapshotStore = $this->prophesize(SnapshotStoreInterface::class);

        $stream->each(function (DomainMessage $domainMessage) use ($snapshotStore, $aggregateRoot) {
            $snapshotStore->has(
                $aggregateRoot->getAggregateRootId(),
                $domainMessage->getVersion()
            )->shouldBeCalled()->willReturn(false);

            $snapshotStore->save(Argument::type(Snapshot::class))->shouldBeCalled();
        });

        $strategy = $this->prophesize(SnapshotStrategyInterface::class);
        $strategy->isFulfilled(new StreamName('event_stream'), $aggregateRoot)->shouldBeCalled()->willReturn(true);

        $snapshotter = new Snapshotter($snapshotStore->reveal(), $strategy->reveal());

        $repo = new AggregateRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            $snapshotter
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

        $strategy = $this->prophesize(SnapshotStrategyInterface::class);
        $snapshotter = new Snapshotter($snapshotStore->reveal(), $strategy->reveal());

        $this->eventStore->fromVersion(
            new StreamName('event_stream'),
            $aggregateRoot->getAggregateRootId(),
            $version + 1
        )->shouldBeCalled()->willReturn($stream);

        $repo = new AggregateRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            $snapshotter
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
