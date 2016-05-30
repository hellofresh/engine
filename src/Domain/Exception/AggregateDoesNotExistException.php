<?php

namespace HelloFresh\Engine\Domain\Exception;

use HelloFresh\Engine\Domain\AggregateIdInterface;

class AggregateDoesNotExistException extends \RuntimeException
{
    public function __construct(AggregateIdInterface $aggregateId)
    {
        parent::__construct(sprintf('Aggregate with ID of "%s" does not exist!', (string)$aggregateId));
    }
}
