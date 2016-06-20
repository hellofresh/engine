<?php

namespace HelloFresh\Engine\EventStore;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\Domain\StreamName;

interface EventStoreInterface
{
    /**
     * @param EventStream $events
     */
    public function append(EventStream $events);

    /**
     * @param StreamName $streamName
     * @param $id
     * @return EventStream
     */
    public function getEventsFor(StreamName $streamName, $id);

    /**
     * @param StreamName $streamName
     * @param AggregateIdInterface $aggregateId
     * @param $version
     * @return EventStream
     */
    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version);

    /**
     * @param StreamName $streamName
     * @param AggregateIdInterface $aggregateId
     * @return int
     */
    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId);
}
