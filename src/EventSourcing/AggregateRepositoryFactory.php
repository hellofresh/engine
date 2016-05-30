<?php

namespace HelloFresh\Engine\EventSourcing;

use Collections\Dictionary;
use Collections\MapInterface;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventStore\Adapter\InMemoryAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\InMemorySnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;

class AggregateRepositoryFactory implements AggregateRepositoryFactoryInterface
{
    /**
     * @var MapInterface
     */
    private $config;

    /**
     * AggregateRepositoryFactory constructor.
     * @param MapInterface $config
     */
    public function __construct($config = null)
    {
        if (!$config instanceof MapInterface) {
            $config = new Dictionary($config);
        }

        $this->config = new Dictionary([
            'event_bus' => [
                'service' => new SimpleEventBus()
            ],
            'event_store' => [
                'adapter' => InMemoryAdapter::class
            ],
            'snapshot_store' => [
                'adapter' => InMemorySnapshotAdapter::class
            ]
        ]);
        $this->config->concat($config);
    }

    public function build()
    {
        $eventBus = $this->config->get('event_bus')->get('service');
        $eventStore = $this->configureEventStore($this->config->get('event_store'));
        $snapshotStore = $this->configureSnapshotStore($this->config->get('snapshot_store'));

        return new EventSourcingRepository($eventStore, $eventBus, $snapshotStore);
    }

    private function configureEventStore(MapInterface $config)
    {
        $adapterName = $config->get('adapter');
        $arguments = $config->tryGet('arguments', []);

        $adapter = new $adapterName(...$arguments);

        return new EventStore($adapter);
    }

    private function configureSnapshotStore(MapInterface $config)
    {
        $adapterName = $config->get('adapter');
        $arguments = $config->tryGet('arguments', []);

        $adapter = new $adapterName(...$arguments);

        return new SnapshotStore($adapter);
    }
}
