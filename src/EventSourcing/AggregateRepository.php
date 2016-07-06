<?php

namespace HelloFresh\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\Domain\StreamName;
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
     * @var bool
     */
    protected $oneStreamPerAggregate;

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
    public function load($id, $aggregateType, StreamName $streamName = null)
    {
        if ($this->snapshotter) {
            $aggregateRoot = $this->loadFromSnapshotStore($id, $streamName);

            if ($aggregateRoot) {
                return $aggregateRoot;
            }
        }

        $streamName = $this->determineStreamName($streamName);

        return $aggregateType::reconstituteFromHistory($this->eventStore->getEventsFor($streamName, $id));
    }

    /**
     * {@inheritDoc}
     */
    public function save(AggregateRootInterface $aggregate, StreamName $streamName = null)
    {
        $streamName = $this->determineStreamName($streamName);
        $eventStream = new EventStream($streamName, $aggregate->getUncommittedEvents());

        $this->eventStore->append($eventStream);

        $eventStream->each(function (DomainMessage $domainMessage) {
            $this->eventBus->publish($domainMessage->getPayload());
        })->each(function (DomainMessage $domainMessage) use ($streamName, $aggregate) {
            if ($this->snapshotter) {
                $this->snapshotter->take($streamName, $aggregate, $domainMessage);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    private function loadFromSnapshotStore(AggregateIdInterface $aggregateId, StreamName $streamName = null)
    {
        $snapshot = $this->snapshotter->get($aggregateId);

        if (null === $snapshot) {
            return null;
        }

        $streamName = $this->determineStreamName($streamName);
        $aggregateRoot = $snapshot->getAggregate();
        $stream = $this->eventStore->fromVersion($streamName, $aggregateId, $snapshot->getVersion() + 1);

        if (!$stream->getIterator()->valid()) {
            return $aggregateRoot;
        }

        $aggregateRoot->replay($stream);

        return $aggregateRoot;
    }

    /**
     * Default stream name generation.
     * Override this method in an extending repository to provide a custom name
     *
     * @param StreamName $streamName
     * @return StreamName
     * @internal param null|string $aggregateId
     */
    protected function determineStreamName(StreamName $streamName = null)
    {
        if (null === $streamName) {
            return new StreamName('event_stream');
        }

        return $streamName;
    }
}
