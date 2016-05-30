<?php

namespace HelloFresh\Engine\Domain;

interface DomainEventInterface
{
    /**
     * @return \DateTimeImmutable
     */
    public function occurredOn();
}
