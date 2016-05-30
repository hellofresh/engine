<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;

interface SnapshotStoreAdapterInterface
{
    public function byId(AggregateIdInterface $id);

    public function save(Snapshot $snapshot);

    public function has(AggregateIdInterface $id, $version);
}
