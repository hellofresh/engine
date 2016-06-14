<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use Collections\ArrayList;
use Collections\Dictionary;
use Collections\MapInterface;
use Collections\VectorInterface;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\StreamName;
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

    public function save(StreamName $streamName, DomainMessage $event)
    {
        $id = (string)$event->getId();
        $name = (string)$streamName;
        $events = $this->events->tryGet($name);

        if (!$events) {
            $events = new Dictionary();
            $this->events->add($name, $events);
        }

        if (!$events->containsKey($id)) {
            $events->add($id, new ArrayList());
        }

        $events->get($id)->add($event);
    }

    public function getEventsFor(StreamName $streamName, $id)
    {
        $id = (string)$id;
        $name = (string)$streamName;
        /** @var MapInterface $events */
        try {
            $events = $this->events->get($name);
        } catch (\OutOfBoundsException $e) {
            throw new EventStreamNotFoundException("Stream $name not found", $e->getCode(), $e);
        }

        if (!$events->containsKey($id)) {
            throw new EventStreamNotFoundException();
        }

        return $events->get($id);
    }

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version)
    {
        $name = (string)$streamName;
        /** @var MapInterface $events */
        $events = $this->events->get($name);
        /** @var VectorInterface $aggregateEvents */
        $aggregateEvents = $events->get((string)$aggregateId);

        return $aggregateEvents->filter(function (DomainMessage $message) use ($aggregateId) {
            return $message->getId() === $aggregateId;
        })->filter(function (DomainMessage $message) use ($version) {
            return $message->getVersion() >= $version;
        });
    }

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId)
    {
        $name = (string)$streamName;
        /** @var MapInterface $events */
        $events = $this->events->get($name);
        /** @var MapInterface $stream */
        $stream = $events->get((string)$aggregateId);

        return $stream->count();
    }
}
