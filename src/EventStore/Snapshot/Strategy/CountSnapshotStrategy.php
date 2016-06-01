<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Strategy;

use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\EventStore\EventStoreInterface;

class CountSnapshotStrategy implements SnapshotStrategyInterface
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * CountSnapshotStrategy constructor.
     * @param EventStoreInterface $eventStore
     * @param int $count
     */
    public function __construct(EventStoreInterface $eventStore, $count = 100)
    {
        $this->count = $count;
        $this->eventStore = $eventStore;
    }

    /**
     * @inheritdoc
     */
    public function isFulfilled(AggregateRootInterface $aggregate)
    {
        $countOfEvents = $this->eventStore->countEventsFor($aggregate->getAggregateRootId());

        return $countOfEvents && (($countOfEvents % $this->count) === 0);
    }
}
