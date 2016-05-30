<?php

namespace HelloFresh\Engine\EventStore;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\EventStream;

interface EventStoreInterface
{
    public function append(EventStream $events);

    public function getEventsFor($id);

    public function fromVersion(AggregateIdInterface $aggregateId, $version);

    public function countEventsFor(AggregateIdInterface $aggregateId);
}
