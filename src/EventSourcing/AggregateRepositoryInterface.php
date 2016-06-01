<?php

namespace HelloFresh\Engine\EventSourcing;

use HelloFresh\Engine\Domain\AggregateRootInterface;

interface AggregateRepositoryInterface
{
    public function load($id, $aggregateType);

    public function save(AggregateRootInterface $aggregate);
}
