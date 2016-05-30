<?php

namespace HelloFresh\Engine\EventStore\Snapshot;

use HelloFresh\Engine\Domain\AggregateIdInterface;

interface SnapshotStoreInterface
{
    /**
     * @param AggregateIdInterface $id
     * @return Snapshot
     */
    public function byId(AggregateIdInterface $id);

    /**
     * @param AggregateIdInterface $id
     * @param $version
     * @return bool
     */
    public function has(AggregateIdInterface $id, $version);

    /**
     * @param Snapshot $snapshot
     * @return void
     */
    public function save(Snapshot $snapshot);
}
