<?php

namespace HelloFresh\Engine\EventBus;

use Collections\Vector;
use Collections\Queue;
use Collections\VectorInterface;
use HelloFresh\Engine\Domain\DomainEventInterface;

/**
 * Simple synchronous publishing of events.
 */
class SimpleEventBus implements EventBusInterface
{
    /**
     * @var VectorInterface
     */
    private $eventListeners;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var bool
     */
    private $isPublishing = false;

    /**
     * SimpleEventBus constructor.
     */
    public function __construct()
    {
        $this->eventListeners = new Vector();
        $this->queue = new Queue();
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(EventListenerInterface $eventListener)
    {
        $this->eventListeners->add($eventListener);
    }

    /**
     * {@inheritDoc}
     */
    public function publish(DomainEventInterface $event)
    {
        $this->queue->enqueue($event);

        if (!$this->isPublishing && !$this->queue->isEmpty()) {
            $this->isPublishing = true;
            try {
                while (!$this->queue->isEmpty()) {
                    $this->processEvent($this->queue->pop());
                };
            } finally {
                $this->isPublishing = false;
            }
        }
    }

    private function processEvent(DomainEventInterface $event)
    {
        $this->eventListeners->filter(function (EventListenerInterface $eventListener) use ($event) {
            return $eventListener->isSubscribedTo($event);
        })->each(function (EventListenerInterface $eventListener) use ($event) {
            $eventListener->handle($event);
        });
    }
}
