<?php

namespace HelloFresh\Engine\EventDispatcher;

/**
 * Base type for an event dispatcher.
 */
interface EventDispatcherInterface
{
    /**
     * Dispatches an event
     * @param string $eventName The name of the event
     * @param array ...$arguments
     */
    public function dispatch($eventName, ...$arguments);
}
