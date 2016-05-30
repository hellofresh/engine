<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter;

use Collections\Dictionary;
use Collections\MapInterface;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;

class InMemorySnapshotAdapter implements SnapshotStoreAdapterInterface
{
    /**
     * @var MapInterface
     */
    private $snapshots;

    public function __construct()
    {
        $this->snapshots = new Dictionary();
    }

    public function byId(AggregateIdInterface $id)
    {
        return $this->snapshots->tryGet((string)$id);
    }

    public function save(Snapshot $snapshot)
    {
        $this->snapshots->add((string)$snapshot->getAggregateId(), $snapshot);
    }

    public function has(AggregateIdInterface $id, $version)
    {
        return $this->snapshots->containsKey((string)$id);
    }
}
