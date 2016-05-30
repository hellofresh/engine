<?php

namespace HelloFresh\Tests\Engine\Mock;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\EventSourcing\AggregateRootTrait;

class AggregateRoot implements AggregateRootInterface
{
    use AggregateRootTrait;

    /**
     * @var AggregateIdInterface
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $name;

    protected function __construct()
    {

    }

    public static function create(AggregateIdInterface $aggregateId, $name)
    {
        $aggregate = new static();
        $aggregate->recordThat(new AggregateRootCreated($aggregateId, $name));
        $aggregate->recordThat(new SomethingDone());

        return $aggregate;
    }

    /**
     * @return AggregateIdInterface
     */
    public function getAggregateRootId()
    {
        return $this->aggregateId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function whenAggregateRootCreated(AggregateRootCreated $event)
    {
        $this->aggregateId = $event->getAggregateId();
        $this->name = $event->getName();
    }

    public function getEventStream()
    {
        return new EventStream($this->uncommittedEvents);
    }
}
