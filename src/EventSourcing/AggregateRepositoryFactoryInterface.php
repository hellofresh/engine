<?php

namespace HelloFresh\Engine\EventSourcing;

interface AggregateRepositoryFactoryInterface
{
    public function build();
}
