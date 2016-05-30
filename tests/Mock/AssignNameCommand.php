<?php

namespace HelloFresh\Tests\Engine\Mock;


class AssignNameCommand
{
    /**
     * @var string
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $name;

    /**
     * AssignNameCommand constructor.
     * @param string $aggregateId
     * @param string $name
     */
    public function __construct($aggregateId, $name)
    {
        $this->aggregateId = $aggregateId;
        $this->name = $name;
    }

    /**
     * @return string
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
