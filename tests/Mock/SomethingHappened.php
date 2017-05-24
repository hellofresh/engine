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
    public function __construct(\DateTimeInterface $dateTime = null)
    {
        $this->occurredOn = $dateTime === null ? $dateTime : new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }
}
