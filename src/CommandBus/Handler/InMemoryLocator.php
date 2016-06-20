<?php

namespace HelloFresh\Engine\CommandBus\Handler;

use HelloFresh\Engine\CommandBus\Exception\MissingHandlerException;

class InMemoryLocator implements HandlerLocatorInterface
{
    /**
     * @var object[]
     */
    protected $handlers = [];

    /**
     * @param array $commandClassToHandlerMap
     */
    public function __construct(array $commandClassToHandlerMap = [])
    {
        $this->addHandlers($commandClassToHandlerMap);
    }

    /**
     * Bind a handler instance to receive all commands with a certain class
     *
     * @param object $handler Handler to receive class
     * @param string $commandClassName Command class e.g. "My\TaskAddedCommand"
     */
    public function addHandler($handler, $commandClassName)
    {
        $this->handlers[$commandClassName] = $handler;
    }

    /**
     * Allows you to add multiple handlers at once.
     * @param array $commandClassToHandlerMap
     */
    protected function addHandlers(array $commandClassToHandlerMap)
    {
        foreach ($commandClassToHandlerMap as $commandClass => $handler) {
            $this->addHandler($handler, $commandClass);
        }
    }

    /**
     * Returns the handler bound to the command's class name.
     *
     * @param string $commandName
     *
     * @return object
     */
    public function getHandlerForCommand($commandName)
    {
        if (!isset($this->handlers[$commandName])) {
            throw MissingHandlerException::forCommand($commandName);
        }

        return $this->handlers[$commandName];
    }
}
