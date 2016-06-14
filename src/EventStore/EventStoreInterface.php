<?php

namespace HelloFresh\Engine\EventStore;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\Domain\StreamName;

interface EventStoreInterface
{
    public function append(EventStream $events);

    public function getEventsFor(StreamName $streamName, $id);

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version);

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId);
}
