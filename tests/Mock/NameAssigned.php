<?php

namespace HelloFresh\Tests\Engine\Mock;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainEventInterface;

class NameAssigned implements DomainEventInterface
{
    /**
     * @var \DateTime
     */
    private $occurredOn;

    /**
     * @var AggregateIdInterface
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $name;

    /**
     * SomethingHappened constructor.
     * @param AggregateIdInterface $aggregateId
     * @param $name
     */
    public function __construct(AggregateIdInterface $aggregateId, $name)
    {
        $this->occurredOn = new \DateTime();
        $this->aggregateId = $aggregateId;
        $this->name = $name;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * @return AggregateIdInterface
     */
    public function getAggregateId()
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
}
