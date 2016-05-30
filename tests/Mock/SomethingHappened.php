<?php

namespace HelloFresh\Tests\Engine\Mock;

use HelloFresh\Engine\Domain\DomainEventInterface;

class SomethingHappened implements DomainEventInterface
{
    /**
     * @var \DateTime
     */
    private $occurredOn;

    /**
     * SomethingHappened constructor.
     */
    public function __construct()
    {
        $this->occurredOn = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }
}
