<?php

namespace HelloFresh\Engine\CommandBus;

use HelloFresh\Engine\CommandBus\Exception\CanNotInvokeHandlerException;
use HelloFresh\Engine\CommandBus\Exception\MissingHandlerException;
use League\Tactician\CommandBus;
use League\Tactician\Exception as TacticianException;

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
        try {
            $this->commandBus->handle($command);
        } catch (TacticianException\MissingHandlerException $e) {
            throw new MissingHandlerException($e->getMessage(), $e->getCode(), $e);
        } catch (TacticianException\CanNotInvokeHandlerException $e) {
            throw new CanNotInvokeHandlerException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
