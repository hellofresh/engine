<?php

namespace HelloFresh\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateRootInterface;

interface EventSourcingRepositoryInterface
{
    public function load($id, $aggregateType);

    public function save(AggregateRootInterface $aggregate);
}
