<?php

namespace HelloFresh\Engine\EventDispatcher;

/**
 * Base type for an event listener.
 */
interface EventListenerInterface
{
    /**
     * Adds a listener to an event
     * @param string $eventName The event name
     * @param callable $callable The callable that will be called when the event happens
     */
    public function addListener($eventName, callable $callable);
}
