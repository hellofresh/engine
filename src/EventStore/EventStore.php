<?php

namespace HelloFresh\Engine\EventStore;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\EventStore\Adapter\EventStoreAdapterInterface;

class EventStore implements EventStoreInterface
{
    /**
     * @var EventStoreAdapterInterface
     */
    private $adapter;

    /**
     * EventStore constructor.
     * @param EventStoreAdapterInterface $adapter
     */
    public function __construct(EventStoreAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function append(EventStream $events)
    {
        $streamName = $events->getName();
        $events->each(function (DomainMessage $event) use ($streamName) {
            $this->adapter->save($streamName, $event);
        });
    }

    public function getEventsFor(StreamName $streamName, $id)
    {
        $stream = $this->adapter->getEventsFor($streamName, $id);

        return new EventStream($streamName, $stream);
    }

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version)
    {
        $stream = $this->adapter->fromVersion($streamName, $aggregateId, $version);

        return new EventStream($streamName, $stream);
    }

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId)
    {
        return $this->adapter->countEventsFor($streamName, $aggregateId);
    }
}
