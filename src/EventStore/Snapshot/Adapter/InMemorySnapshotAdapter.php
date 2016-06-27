<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter;

use Collections\Map;
use Collections\MapInterface;
use Collections\Pair;
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
        $this->snapshots = new Map();
    }

    public function byId(AggregateIdInterface $id)
    {
        return $this->snapshots->get((string)$id);
    }

    public function save(Snapshot $snapshot)
    {
        $this->snapshots->add(new Pair((string)$snapshot->getAggregateId(), $snapshot));
    }

    public function has(AggregateIdInterface $id, $version)
    {
        return $this->snapshots->containsKey((string)$id);
    }
}
