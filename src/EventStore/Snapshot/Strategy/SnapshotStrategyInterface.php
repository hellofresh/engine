<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Strategy;

use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\StreamName;

interface SnapshotStrategyInterface
{
    /**
     * Checks if a condition is fulfilled
     * @param StreamName $streamName
     * @param AggregateRootInterface $aggregate
     * @return bool if the condition is fulfilled returns TRUE, otherwise FALSE
     */
    public function isFulfilled(StreamName $streamName, AggregateRootInterface $aggregate);
}
