<?php

namespace HelloFresh\Engine\CommandBus;

use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Middleware;
use League\Tactician\Plugins\LockingMiddleware;

class TacticianCommandBus implements CommandBusInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var InMemoryLocator
     */
    private $commandLocator;

    /**
     * @param Middleware[]|null $middleware
     */
    public function __construct(array $middleware = null)
    {
        $this->commandLocator = new InMemoryLocator();
        $this->commandBus = new CommandBus(
            $middleware ?: $this->createDefaultMiddlewares($this->commandLocator)
        );
    }

    /**
     * @inheritDoc
     */
    public function subscribe($commandName, $handler)
    {
        \Assert\that($commandName)->notEmpty()->string();

        $this->commandLocator->addHandler($handler, $commandName);
    }

    /**
     * @inheritDoc
     */
    public function execute($command)
    {
        $this->commandBus->handle($command);
    }

    /**
     * Create a default set op middlewares to use within the tactician command bus.
     *
     * @param HandlerLocator $commandLocator
     * @return Middleware[]
     */
    public static function createDefaultMiddlewares(HandlerLocator $commandLocator)
    {
        return [
            new LockingMiddleware(),
            static::createDefaultCommandHandlerMiddleware($commandLocator)
        ];
    }

    /**
     * Create a default command handle middleware for tactician that uses the same convention as SimpleCommandBus.
     *
     * @param HandlerLocator $commandLocator
     * @return Middleware
     */
    public static function createDefaultCommandHandlerMiddleware(HandlerLocator $commandLocator)
    {
        return new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $commandLocator,
            new HandleInflector()
        );
    }
}
