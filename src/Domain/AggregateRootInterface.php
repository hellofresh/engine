<?php

namespace HelloFresh\Engine\Domain;

/**
 * Defines basic methods for an aggregate id
 */
interface AggregateRootInterface
{
    /**
     * @return EventStream
     */
    public function getUncommittedEvents();

    /**
     * @return AggregateIdInterface
     */
    public function getAggregateRootId();


    /**
     * Replay past events
     *
     * @param EventStream $historyEvents
     *
     * @throws \RuntimeException
     */
    public function replay(EventStream $historyEvents);
}
