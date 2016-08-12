<?php

namespace HelloFresh\Engine\Provider;

use HelloFresh\Engine\CommandBus\Handler\InMemoryLocator;
use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\CommandBus\TacticianCommandBus;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Engine\EventSourcing\AggregateRepository;
use HelloFresh\Engine\EventStore\Adapter\DbalAdapter;
use HelloFresh\Engine\EventStore\Adapter\InMemoryAdapter;
use HelloFresh\Engine\EventStore\Adapter\MongoAdapter;
use HelloFresh\Engine\EventStore\Adapter\MongoDbAdapter;
use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use HelloFresh\Engine\EventStore\EventStore;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\DbalSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\InMemorySnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\Adapter\RedisSnapshotAdapter;
use HelloFresh\Engine\EventStore\Snapshot\SnapshotStore;
use HelloFresh\Engine\EventStore\Snapshot\Snapshotter;
use HelloFresh\Engine\EventStore\Snapshot\Strategy\CountSnapshotStrategy;
use HelloFresh\Engine\Serializer\Adapter\JmsSerializerAdapter;
use HelloFresh\Engine\Serializer\Adapter\PhpJsonSerializerAdapter;
use HelloFresh\Engine\Serializer\Adapter\PhpSerializerAdapter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class EngineServiceProvider implements ServiceProviderInterface
{
    protected $prefix;

    /**
     * @param string $prefix Prefix name used to register the service provider in Silex.
     */
    public function __construct($prefix = 'engine')
    {
        if (empty($prefix)) {
            throw new \InvalidArgumentException('The specified prefix is not valid.');
        }
        $this->prefix = $prefix;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple["$this->prefix.config"] = $this->defineDefaultConfig();
        $pimple["$this->prefix.command_bus"] = $this->setUpCommandBus($pimple);
        $pimple["$this->prefix.event_bus"] = $this->setUpEventBus();
        $pimple["$this->prefix.event_store"] = $this->setUpEventStore($pimple);
        $pimple["$this->prefix.snapshot_store"] = $this->setUpSnapshotStore($pimple);
        $pimple["$this->prefix.snapshotter"] = $this->setUpSnapshotter($pimple);
        $pimple["$this->prefix.repository.aggregate"] = $this->setUpAggregateRepository();
        $pimple["$this->prefix.serializer"] = $this->setUpSerializer($pimple);
    }

    private function defineDefaultConfig()
    {
        return [
            'command_bus' => [
                'adapter' => 'simple'
            ],
            'event_store' => [
                'adapter' => 'in_memory'
            ],
            'snapshot_store' => [
                'adapter' => 'in_memory'
            ],
            'snapshotter' => [
                'strategy' => 'count',
                'strategies' => [
                    'count' => [
                        'events' => 100
                    ]
                ]
            ],
            'serializer' => [
                'adapter' => 'php_json'
            ]
        ];
    }

    private function setUpCommandBus(Container $pimple)
    {
        $pimple["$this->prefix.command_bus.locator"] = function () {
            return new InMemoryLocator();
        };

        $pimple["$this->prefix.command_bus.simple"] = function (Container $c) {
            $locator = $c["$this->prefix.command_bus.locator"];
            $mapper = $c["$this->prefix.command_bus.handlers"];

            foreach ($mapper as $commandName => $handler) {
                $locator->addHandler($handler, $commandName);
            }

            return new SimpleCommandBus($c["$this->prefix.command_bus.locator"]);
        };

        $pimple["$this->prefix.command_bus.tactician"] = function (Container $c) {
            $locator = $c['tactician.locator'];
            $mapper = $c["$this->prefix.command_bus.handlers"];

            foreach ($mapper as $commandName => $handler) {
                $locator->addHandler($handler, $commandName);
            }

            return new TacticianCommandBus($c['tactician.command_bus']);
        };

        return function (Container $c) {
            $adapterName = $c["$this->prefix.config"]['command_bus']['adapter'];
            $service = "$this->prefix.command_bus.$adapterName";

            if (!isset($c[$service])) {
                throw new \InvalidArgumentException('Invalid event store adapter provided');
            }

            return $c[$service];
        };
    }

    private function setUpEventBus()
    {
        return function (Container $c) {
            $eventBus = new SimpleEventBus();
            $listeners = isset($c["$this->prefix.event_bus.listeners"]) ? $c["$this->prefix.event_bus.listeners"] : [];

            foreach ($listeners as $listener) {
                $eventBus->subscribe($listener);
            }

            return $eventBus;
        };
    }

    private function setUpEventStore(Container $pimple)
    {
        $pimple["$this->prefix.event_store.adapter.in_memory"] = function () {
            return new InMemoryAdapter();
        };

        $pimple["$this->prefix.event_store.adapter.redis"] = function (Container $c) {
            $predisClient = $c["$this->prefix.config"]['event_store']['adapters']['redis']['client'];

            if (!isset($c[$predisClient])) {
                throw new \InvalidArgumentException('Invalid event store predis client provided');
            }

            return new RedisAdapter($c[$predisClient], $c["$this->prefix.serializer"]);
        };

        $pimple["$this->prefix.event_store.adapter.dbal"] = function (Container $c) {
            $connection = $c["$this->prefix.config"]['event_store']['adapters']['dbal']['connection'];

            if (!isset($c[$connection])) {
                throw new \InvalidArgumentException('Invalid event store  doctrine connection provided');
            }

            return new DbalAdapter($c[$connection], $c["$this->prefix.serializer"]);
        };

        $pimple["$this->prefix.event_store.adapter.mongo"] = function (Container $c) {
            $mongoClient = $c["$this->prefix.config"]['event_store']['adapters']['mongo']['client'];
            $dbName = $c["$this->prefix.config"]['event_store']['adapters']['mongo']['db_name'];

            if (!isset($c[$mongoClient])) {
                throw new \InvalidArgumentException('Invalid event store mongo client provided');
            }

            return new MongoAdapter($c[$mongoClient], $c["$this->prefix.serializer"], $dbName);
        };

        $pimple["$this->prefix.event_store.adapter.mongodb"] = function (Container $c) {
            $mongodbClient = $c["$this->prefix.config"]['event_store']['adapters']['mongodb']['client'];
            $dbName = $c["$this->prefix.config"]['event_store']['adapters']['mongodb']['db_name'];

            if (!isset($c[$mongodbClient])) {
                throw new \InvalidArgumentException('Invalid event store mongodb client provided');
            }

            return new MongoDbAdapter($c[$mongodbClient], $c["$this->prefix.serializer"], $dbName);
        };

        return function (Container $c) {
            $adapterName = $c["$this->prefix.config"]['event_store']['adapter'];
            $service = "$this->prefix.event_store.adapter.$adapterName";

            if (!isset($c[$service])) {
                throw new \InvalidArgumentException('Invalid event store adapter provided');
            }

            return new EventStore($c[$service]);
        };
    }

    private function setUpSnapshotStore(Container $pimple)
    {
        $pimple["$this->prefix.snapshot_store.adapter.in_memory"] = function () {
            return new InMemorySnapshotAdapter();
        };

        $pimple["$this->prefix.snapshot_store.adapter.redis"] = function (Container $c) {
            $predisClient = $c["$this->prefix.config"]['snapshot_store']['adapters']['redis']['client'];

            if (!isset($c[$predisClient])) {
                throw new \InvalidArgumentException('Invalid snapshot predis client provided');
            }

            return new RedisSnapshotAdapter($c[$predisClient], $c["$this->prefix.serializer"]);
        };

        $pimple["$this->prefix.snapshot_store.adapter.dbal"] = function (Container $c) {
            $connection = $c["$this->prefix.config"]['snapshot_store']['adapters']['dbal']['connection'];
            $tableName = $c["$this->prefix.config"]['snapshot_store']['adapters']['dbal']['table_name'];

            if (!isset($c[$connection])) {
                throw new \InvalidArgumentException('Invalid snapshot doctrine connection provided');
            }

            return new DbalSnapshotAdapter($c[$connection], $c["$this->prefix.serializer"], $tableName);
        };

        return function (Container $c) {
            $adapterName = $c["$this->prefix.config"]['snapshot_store']['adapter'];
            $service = "$this->prefix.snapshot_store.adapter.$adapterName";

            if (!isset($c[$service])) {
                throw new \InvalidArgumentException('Invalid snapshot adapter provided');
            }

            return new SnapshotStore($c[$service]);
        };
    }

    private function setUpSnapshotter(Container $pimple)
    {
        $pimple["$this->prefix.snapshotter.strategy.count"] = function (Container $c) {
            $count = $c["$this->prefix.config"]['snapshotter']['strategies']['count']['events'];

            return new CountSnapshotStrategy($c["$this->prefix.event_store"], $count);
        };

        return function (Container $c) {
            $strategyName = $c["$this->prefix.config"]['snapshotter']["strategy"];
            $service = "$this->prefix.snapshotter.strategy.$strategyName";

            if (!isset($c[$service])) {
                throw new \InvalidArgumentException('Invalid snapshotter strategy provided');
            }

            return new Snapshotter($c["$this->prefix.snapshot_store"], $c[$service]);
        };
    }

    private function setUpAggregateRepository()
    {
        return function (Container $c) {
            return new AggregateRepository(
                $c["$this->prefix.event_store"],
                $c["$this->prefix.event_bus"],
                $c["$this->prefix.snapshotter"]
            );
        };
    }

    private function setUpSerializer(Container $pimple)
    {
        $pimple["$this->prefix.serializer.adapter.jms"] = function (Container $c) {
            $jmsClient = $c["$this->prefix.config"]['serializer']['adapters']['jms']['client'];

            if (!isset($c[$jmsClient])) {
                throw new \InvalidArgumentException('Invalid JMS client provided');
            }

            return new JmsSerializerAdapter($c[$jmsClient]);
        };

        $pimple["$this->prefix.serializer.adapter.symfony"] = function (Container $c) {
            $symfonySerializer = $c["$this->prefix.config"]['serializer']['adapters']['symfony']['client'];

            if (!isset($c[$symfonySerializer])) {
                throw new \InvalidArgumentException('Invalid symfony serializer client provided');
            }

            return new JmsSerializerAdapter($c[$symfonySerializer]);
        };

        $pimple["$this->prefix.serializer.adapter.php_json"] = function () {
            return new PhpJsonSerializerAdapter();
        };

        $pimple["$this->prefix.serializer.adapter.php"] = function () {
            return new PhpSerializerAdapter();
        };

        return function (Container $c) {
            $adapterName = $c["$this->prefix.config"]['serializer']['adapter'];
            $service = "$this->prefix.serializer.adapter.$adapterName";

            if (!isset($c[$service])) {
                throw new \InvalidArgumentException('Invalid serializer adapter provided');
            }

            return $c[$service];
        };
    }
}
