<?php

namespace HelloFresh\Engine\EventStore\Snapshot;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\EventStore\Snapshot\Strategy\SnapshotStrategyInterface;

class Snapshotter
{
    /**
     * @var SnapshotStoreInterface
     */
    private $snapshotStore;

    /**
     * @var SnapshotStrategyInterface
     */
    protected $strategy;

    /**
     * Snapshotter constructor.
     * @param SnapshotStoreInterface $snapshotStore
     * @param SnapshotStrategyInterface $strategy
     */
    public function __construct(SnapshotStoreInterface $snapshotStore, SnapshotStrategyInterface $strategy)
    {
        $this->snapshotStore = $snapshotStore;
        $this->strategy = $strategy;
    }

    /**
     * Takes a snapshot
     * @param StreamName $streamName
     * @param AggregateRootInterface $aggregate
     * @param DomainMessage $message - The domain message
     * @return bool
     */
    public function take(StreamName $streamName, AggregateRootInterface $aggregate, DomainMessage $message)
    {
        $id = $aggregate->getAggregateRootId();

        if (!$this->strategy->isFulfilled($streamName, $aggregate)) {
            return false;
        }

        if (!$this->snapshotStore->has($id, $message->getVersion())) {
            $this->snapshotStore->save(Snapshot::take($id, $aggregate, $message->getVersion()));
        }

        return true;
    }

    /**
     * @param AggregateIdInterface $id
     * @return Snapshot
     */
    public function get(AggregateIdInterface $id)
    {
        return $this->snapshotStore->byId($id);
    }
}
