<?php

namespace HelloFresh\Engine\EventBus;

use HelloFresh\Engine\Domain\DomainEventInterface;

/**
 * Publishes events to the subscribed event listeners.
 */
interface EventBusInterface
{
    /**
     * Subscribes the event listener to the event bus.
     *
     * @param EventListenerInterface $eventListener
     */
    public function subscribe(EventListenerInterface $eventListener);

    /**
     * Publishes the events from the domain event stream to the listeners.
     *
     * @param DomainEventInterface $event
     */
    public function publish(DomainEventInterface $event);
}
