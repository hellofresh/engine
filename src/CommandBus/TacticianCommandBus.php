<?php

namespace HelloFresh\Engine\CommandBus;

use League\Tactician\CommandBus;

class TacticianCommandBus implements CommandBusInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * TacticianCommandBus constructor.
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($command)
    {
        $this->commandBus->handle($command);
    }
}
