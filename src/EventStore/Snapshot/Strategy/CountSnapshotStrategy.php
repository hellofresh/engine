<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Strategy;

use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\StreamName;
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
    public function isFulfilled(StreamName $streamName, AggregateRootInterface $aggregate)
    {
        $countOfEvents = $this->eventStore->countEventsFor($streamName, $aggregate->getAggregateRootId());

        return $countOfEvents && (($countOfEvents % $this->count) === 0);
    }
}
