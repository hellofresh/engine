<?php

namespace HelloFresh\Engine\EventSourcing;

use HelloFresh\Engine\Domain\DomainEventInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;

trait AggregateRootTrait
{
    /**
     * @var array
     */
    private $uncommittedEvents = [];
    private $version = -1;

    /**
     * @param EventStream $historyEvents
     * @return static
     */
    public static function reconstituteFromHistory(EventStream $historyEvents)
    {
        $instance = new static();
        $instance->replay($historyEvents);

        return $instance;
    }

    public function getUncommittedEvents()
    {
        $stream = new EventStream($this->uncommittedEvents);
        $this->uncommittedEvents = [];

        return $stream;
    }

    /**
     * Replay past events
     *
     * @param EventStream $historyEvents
     *
     * @throws \RuntimeException
     */
    public function replay(EventStream $historyEvents)
    {
        $historyEvents->each(function (DomainMessage $pastEvent) {
            $this->version++;
            $this->apply($pastEvent->getPayload());
        });
    }

    /**
     * Apply given event
     *
     * @param DomainEventInterface $event
     */
    protected function apply(DomainEventInterface $event)
    {
        $handler = $this->determineEventHandlerMethodFor($event);

        if (!method_exists($this, $handler)) {
            return;
        }

        $this->{$handler}($event);
    }

    protected function recordThat(DomainEventInterface $event)
    {
        $this->apply($event);
        $this->record($event);

        return $this;
    }

    /**
     * Determine event name
     *
     * @param DomainEventInterface $event
     * @return string
     */
    protected function determineEventHandlerMethodFor(DomainEventInterface $event)
    {
        $parts = explode('\\', get_class($event));

        return 'when' . end($parts);
    }

    private function record(DomainEventInterface $event)
    {
        $this->version++;
        $this->uncommittedEvents[] = DomainMessage::recordNow(
            $this->getAggregateRootId(),
            $this->version,
            $event
        );
    }
}
