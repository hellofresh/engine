<?php

namespace HelloFresh\Engine\EventDispatcher;

use Collections\ArrayList;
use Collections\Dictionary;
use Collections\MapInterface;

/**
 * In Memory Event dispatcher implementation.
 */
class InMemoryDispatcher implements EventDispatcherInterface, EventListenerInterface
{
    /**
     * @var MapInterface
     */
    private $listeners;

    /**
     * EventDispatcher constructor.
     */
    public function __construct()
    {
        $this->listeners = new Dictionary();
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($eventName, ...$arguments)
    {
        if (!$this->listeners->containsKey($eventName)) {
            return;
        }

        foreach ($this->listeners->get($eventName) as $listener) {
            $listener(...$arguments);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addListener($eventName, callable $callable)
    {
        if (!$this->listeners->containsKey($eventName)) {
            $this->listeners->add($eventName, new ArrayList());
        }

        $this->listeners->get($eventName)->add($callable);
    }
}
