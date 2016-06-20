<?php

namespace HelloFresh\Engine\CommandBus;

/**
 * Dispatches command objects to the subscribed command handlers.
 */
interface CommandBusInterface
{
    /**
     * Dispatches the command $command to the proper CommandHandler
     *
     * @param mixed $command A command that will be dispatched
     */
    public function execute($command);
}
