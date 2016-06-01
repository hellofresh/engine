<?php

namespace HelloFresh\Engine\EventStore\Snapshot;

use HelloFresh\Engine\Domain\AggregateRootInterface;

interface SnapshotStrategyInterface
{
    /**
     * Checks if a condition is fulfilled
     * @param AggregateRootInterface $aggregate
     * @return bool if the condition is fulfilled returns TRUE, otherwise FALSE
     */
    public function isFulfilled(AggregateRootInterface $aggregate);
}
