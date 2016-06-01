<?php

namespace HelloFresh\Engine\EventStore\Snapshot;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\SnapshotStoreAdapterInterface;

class SnapshotStore implements SnapshotStoreInterface
{
    /**
     * @var SnapshotStoreAdapterInterface
     */
    private $adapter;

    /**
     * SnapshotStore constructor.
     * @param SnapshotStoreAdapterInterface $adapter
     */
    public function __construct(SnapshotStoreAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function byId(AggregateIdInterface $id)
    {
        return $this->adapter->byId($id);
    }

    /**
     * @inheritdoc
     */
    public function has(AggregateIdInterface $id, $version)
    {
        return $this->adapter->has($id, $version);
    }

    /**
     * @inheritdoc
     */
    public function save(Snapshot $snapshot)
    {
        return $this->adapter->save($snapshot);
    }
}
