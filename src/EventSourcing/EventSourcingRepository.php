<?php

namespace HelloFresh\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\EventBus\EventBusInterface;
use HelloFresh\Engine\EventStore\EventStoreInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStoreInterface;

class EventSourcingRepository implements EventSourcingRepositoryInterface
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * @var EventBusInterface
     */
    private $eventBus;

    /**
     * @var \HelloFresh\Engine\EventStore\Snapshot\SnapshotStoreInterface
     */
    private $snapshotStore;

    /**
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     * @param SnapshotStoreInterface $snapshotStore
     */
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        SnapshotStoreInterface $snapshotStore = null
    ) {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->snapshotStore = $snapshotStore;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id, $aggregateType)
    {
        if ($this->snapshotStore) {
            $aggregateRoot = $this->loadFromSnapshotStore($id);

            if ($aggregateRoot) {
                return $aggregateRoot;
            }
        }

        return $aggregateType::reconstituteFromHistory($this->eventStore->getEventsFor($id));
    }

    /**
     * {@inheritDoc}
     */
    public function save(AggregateRootInterface $aggregate)
    {
        $id = $aggregate->getAggregateRootId();
        $eventStream = $aggregate->getUncommittedEvents();

        if ($this->snapshotStore) {
            $countOfEvents = $this->eventStore->countEventsFor($id);
            $version = $countOfEvents;
            
            if ($countOfEvents && (($countOfEvents % 100) === 0) && !$this->snapshotStore->has($id, $version)) {
                $this->snapshotStore->save(Snapshot::take($id, $aggregate, $version));
            }
        }

        $this->eventStore->append($eventStream);
        $eventStream->each(function (DomainMessage $domainMessage) {
            $this->eventBus->publish($domainMessage->getPayload());
        });
    }

    /**
     * {@inheritDoc}
     */
    private function loadFromSnapshotStore(AggregateIdInterface $aggregateId)
    {
        $snapshot = $this->snapshotStore->byId($aggregateId);

        if (null === $snapshot) {
            return null;
        }

        $aggregateRoot = $snapshot->getAggregate();
        $stream = $this->eventStore->fromVersion($aggregateId, $snapshot->getVersion());
        $aggregateRoot->replay($stream);

        return $aggregateRoot;
    }
}
