<?php

namespace HelloFresh\Tests\Engine\EventStore;

use HelloFresh\Engine\EventStore\Adapter\InMemoryAdapter;
use HelloFresh\Engine\EventStore\EventStore;

class InMemoryEventStoreTest extends EventStoreTest
{
    public function setUp()
    {
        $this->eventStore = new EventStore(new InMemoryAdapter());
    }
}
