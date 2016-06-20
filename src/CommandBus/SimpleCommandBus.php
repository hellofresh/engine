<?php

namespace HelloFresh\Engine\CommandBus;

use Collections\Queue;
use HelloFresh\Engine\CommandBus\Exception\CanNotInvokeHandlerException;
use HelloFresh\Engine\CommandBus\Handler\HandlerLocatorInterface;

class SimpleCommandBus implements CommandBusInterface
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var bool
     */
    private $isDispatching = false;

    /**
     * @var HandlerLocatorInterface
     */
    private $handlerLocator;

    /**
     * SimpleCommandBus constructor.
     * @param HandlerLocatorInterface $handlerLocator
     */
    public function __construct(HandlerLocatorInterface $handlerLocator)
    {
        $this->queue = new Queue();
        $this->handlerLocator = $handlerLocator;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($command)
    {
        $this->queue->enqueue($command);

        if (!$this->isDispatching) {
            $this->isDispatching = true;
            try {
                while (!$this->queue->isEmpty()) {
                    $this->processCommand($this->queue->pop());
                }
            } finally {
                $this->isDispatching = false;
            }
        }
    }

    /**
     * @param $command
     */
    private function processCommand($command)
    {
        $handler = $this->handlerLocator->getHandlerForCommand(get_class($command));
        $methodName = 'handler';

        if (!is_callable([$handler, $methodName])) {
            throw CanNotInvokeHandlerException::forCommand(
                $command,
                "Method '{$methodName}' does not exist on handler"
            );
        }

        $handler->handle($command);
    }
}
