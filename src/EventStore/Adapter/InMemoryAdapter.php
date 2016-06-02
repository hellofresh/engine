<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use Collections\ArrayList;
use Collections\Dictionary;
use Collections\MapInterface;
use Collections\VectorInterface;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\EventStore\Exception\EventStreamNotFoundException;

class InMemoryAdapter implements EventStoreAdapterInterface
{
    /**
     * @var MapInterface
     */
    private $events;

    public function __construct()
    {
        $this->events = new Dictionary();
    }

    public function save(DomainMessage $event)
    {
        $id = (string)$event->getId();

        if (!$this->events->containsKey($id)) {
            $this->events->add($id, new ArrayList());
        }

        $this->events->get($id)->add($event);
    }

    public function getEventsFor($id)
    {
        $id = (string)$id;

        if (!$this->events->containsKey($id)) {
            throw new EventStreamNotFoundException();
        }

        return $this->events->get($id);
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        /** @var VectorInterface $aggregateEvents */
        $aggregateEvents = $this->events->get((string)$aggregateId);

        return $aggregateEvents->filter(function (DomainMessage $message) use ($aggregateId) {
            return $message->getId() === $aggregateId;
        })->filter(function (DomainMessage $message) use ($version) {
            return $message->getVersion() >= $version;
        });
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        /** @var MapInterface $stream */
        $stream = $this->events->get((string)$aggregateId);

        return $stream->count();
    }
}
