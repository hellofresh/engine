<?php

namespace HelloFresh\Engine\CommandBus\Handler;

use HelloFresh\Engine\CommandBus\Exception\MissingHandlerException;

/**
 * Service locator for handler objects
 */
interface HandlerLocatorInterface
{
    /**
     * Retrieves the handler for a specified command
     *
     * @param string $commandName
     *
     * @return object
     *
     * @throws MissingHandlerException
     */
    public function getHandlerForCommand($commandName);
}
