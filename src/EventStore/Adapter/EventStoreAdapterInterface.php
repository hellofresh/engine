<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\StreamName;

interface EventStoreAdapterInterface
{
    public function save(StreamName $streamName, DomainMessage $events);

    public function getEventsFor(StreamName $streamName, $id);

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version);

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId);
}
