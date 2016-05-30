<?php

namespace HelloFresh\Engine\EventBus;

use HelloFresh\Engine\Domain\DomainEventInterface;

/**
 * Handles dispatched events.
 */
interface EventListenerInterface
{
    /**
     * @param DomainEventInterface $event
     */
    public function handle(DomainEventInterface $event);

    /**
     * @param DomainEventInterface $event
     * @return bool
     */
    public function isSubscribedTo(DomainEventInterface $event);
}
