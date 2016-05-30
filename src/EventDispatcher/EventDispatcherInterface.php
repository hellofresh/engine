<?php

namespace HelloFresh\Engine\EventDispatcher;

/**
 * Base type for an event dispatcher.
 */
interface EventDispatcherInterface
{
    /**
     * @param $eventName
     * @param array ...$arguments
     */
    public function dispatch($eventName, ...$arguments);

    /**
     * @param string $eventName
     * @param callable $callable
     */
    public function addListener($eventName, callable $callable);
}