<?php

namespace HelloFresh\Engine\CommandBus;

use Assert\Assertion;
use Collections\Dictionary;
use Collections\MapInterface;
use Collections\Queue;

class TacticianCommandBus implements CommandBusInterface
{
    /**
     * @var MapInterface
     */
    private $commandHandlers;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var bool
     */
    private $isDispatching = false;

    /**
     * SimpleCommandBus constructor.
     */
    public function __construct()
    {
        $this->commandHandlers = new Dictionary();
        $this->queue = new Queue();
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe($commandName, $handler)
    {
        \Assert\that($commandName)->notEmpty()->string();
        $this->commandHandlers->add($commandName, $handler);
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
        $this->commandHandlers->filterWithKey(function ($commandName, $handler) use ($command) {
            return $commandName === get_class($command);
        })->each(function ($handler) use ($command) {
            Assertion::methodExists('handle', $handler);
            $handler->handle($command);
        });
    }
}
