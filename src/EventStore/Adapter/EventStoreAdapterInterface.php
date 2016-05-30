<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;

interface EventStoreAdapterInterface
{
    public function save(DomainMessage $events);

    public function getEventsFor($id);

    public function fromVersion(AggregateIdInterface $aggregateId, $version);

    public function countEventsFor(AggregateIdInterface $aggregateId);
}
