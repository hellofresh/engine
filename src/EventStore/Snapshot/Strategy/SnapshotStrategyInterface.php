<?php

namespace HelloFresh\Engine\EventStore\Snapshot;

class CountSnapshotStrategy
{
    /**
     * @var int
     */
    private $count;

    /**
     * CountSnapshotStrategy constructor.
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }

    

}
