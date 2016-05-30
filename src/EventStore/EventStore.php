<?php

namespace HelloFresh\Engine\EventStore;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;
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
        $events->each(function (DomainMessage $event) {
            $this->adapter->save($event);
        });
    }

    public function getEventsFor($id)
    {
        return new EventStream($this->adapter->getEventsFor($id));
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        return new EventStream($this->adapter->fromVersion($aggregateId, $version));
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        return $this->adapter->countEventsFor($aggregateId);
    }
}
