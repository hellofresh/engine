<?php

namespace HelloFresh\Engine\CommandBus;

use HelloFresh\Engine\EventDispatcher\EventDispatcherInterface;

class EventDispatchingCommandBus implements CommandBusInterface
{
    const EVENT_COMMAND_SUCCESS = 'engine.command_handling.command_success';
    const EVENT_COMMAND_FAILURE = 'engine.command_handling.command_failure';

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(CommandBusInterface $commandBus, EventDispatcherInterface $dispatcher)
    {
        $this->commandBus = $commandBus;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($command)
    {
        try {
            $this->commandBus->execute($command);
            $this->dispatcher->dispatch(self::EVENT_COMMAND_SUCCESS, ['command' => $command]);
        } catch (\Exception $e) {
            $this->dispatcher->dispatch(
                self::EVENT_COMMAND_FAILURE,
                ['command' => $command, 'exception' => $e]
            );
            throw $e;
        }
    }
}
