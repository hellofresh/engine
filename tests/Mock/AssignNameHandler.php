<?php

namespace HelloFresh\Tests\Engine\Mock;


use HelloFresh\Engine\EventSourcing\AggregateRepositoryInterface;

class AssignNameHandler
{
    /**
     * @var AggregateRepositoryInterface
     */
    private $repo;

    /**
     * AssignNameHandler constructor.
     * @param AggregateRepositoryInterface $repo
     */
    public function __construct(AggregateRepositoryInterface $repo)
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
