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

    /**
     * Subscribes the command handler to this CommandBus
     * @param string $commandName The command name to map to
     * @param mixed $handler
     */
    public function subscribe($commandName, $handler);
}
