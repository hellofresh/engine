<?php

namespace HelloFresh\Tests\Engine\Mock;


use HelloFresh\Engine\EventSourcing\EventSourcingRepositoryInterface;

class AssignNameHandler
{
    /**
     * @var EventSourcingRepositoryInterface
     */
    private $repo;

    /**
     * AssignNameHandler constructor.
     * @param EventSourcingRepositoryInterface $repo
     */
    public function __construct(EventSourcingRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function handle(AssignNameCommand $command)
    {
        /** @var AggregateRoot $aggregateRoot */
        $aggregateRoot = $this->repo->load($command->getAggregateId(), AggregateRoot::class);
        $aggregateRoot->assignName($command->getName());

        $this->repo->save($aggregateRoot);
    }
}
