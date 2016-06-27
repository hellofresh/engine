<?php

namespace HelloFresh\Engine\EventSourcing;

use Collections\Map;
use Collections\MapInterface;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventStore\Adapter\InMemoryAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\InMemorySnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;
use HelloFresh\Engine\EventStore\Snapshot\Snapshotter;
use HelloFresh\Engine\EventStore\Snapshot\Strategy\CountSnapshotStrategy;

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
            $config = new Map($config);
        }

        $this->config = new Map([
            'event_bus' => [
                'service' => new SimpleEventBus()
            ],
            'event_store' => [
                'adapter' => InMemoryAdapter::class
            ],
            'snapshotter' => [
                'enabled' => false,
                'store' => [
                    'adapter' => InMemorySnapshotAdapter::class
                ],
                'strategy' => [
                    'name' => CountSnapshotStrategy::class,
                    'arguments' => []
                ]
            ]
        ]);

        if ($config) {
            $this->config->concat($config);
        }
    }

    public function build()
    {
        $eventBus = $this->config->get('event_bus')->get('service');
        $eventStore = $this->configureEventStore($this->config->get('event_store'));
        $snapshotter = null;

        /** @var MapInterface $snapshotterConfig */
        $snapshotterConfig = $this->config->get('snapshotter');

        if (true === $snapshotterConfig->get('enabled')) {
            $snapshotStore = $this->configureSnapshotStore($snapshotterConfig->get('store'));
            $strategy = $this->configureSnapshotStrategy($snapshotterConfig->get('strategy'));
            $snapshotter = new Snapshotter($snapshotStore, $strategy);
        }

        return new AggregateRepository($eventStore, $eventBus, $snapshotter);
    }

    private function configureEventStore(MapInterface $config)
    {
        $adapterName = $config->get('adapter');
        $arguments = $config->get('arguments') ? $config->get('arguments') : [];

        $adapter = new $adapterName(...$arguments);

        return new EventStore($adapter);
    }

    private function configureSnapshotStore(MapInterface $config)
    {
        $adapterName = $config->get('adapter');
        $arguments = $config->get('arguments') ? $config->get('arguments') : [];

        $adapter = new $adapterName(...$arguments);

        return new SnapshotStore($adapter);
    }

    private function configureSnapshotStrategy(MapInterface $config)
    {
        $adapterName = $config->get('name');
        $arguments = $config->get('arguments') ? $config->get('arguments') : [];

        return new $adapterName(...$arguments);
    }
}
