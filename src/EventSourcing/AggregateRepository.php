<?php

namespace HelloFresh\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\EventBus\EventBusInterface;
use HelloFresh\Engine\EventStore\EventStoreInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshotter;

class AggregateRepository implements AggregateRepositoryInterface
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
     * @var Snapshotter
     */
    private $snapshotter;

    /**
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     * @param Snapshotter $snapshotter
     */
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        Snapshotter $snapshotter = null
    ) {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->snapshotter = $snapshotter;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id, $aggregateType)
    {
        if ($this->snapshotter) {
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
        $eventStream = $aggregate->getUncommittedEvents();
        $this->eventStore->append($eventStream);

        $eventStream->each(function (DomainMessage $domainMessage) {
            $this->eventBus->publish($domainMessage->getPayload());
        })->each(function (DomainMessage $domainMessage) use ($aggregate) {
            if ($this->snapshotter) {
                $this->snapshotter->take($aggregate, $domainMessage);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    private function loadFromSnapshotStore(AggregateIdInterface $aggregateId)
    {
        $snapshot = $this->snapshotter->get($aggregateId);

        if (null === $snapshot) {
            return null;
        }

        $aggregateRoot = $snapshot->getAggregate();
        $stream = $this->eventStore->fromVersion($aggregateId, $snapshot->getVersion());
        $aggregateRoot->replay($stream);

        return $aggregateRoot;
    }
}
