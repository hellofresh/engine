<?php

namespace HelloFresh\Tests\Engine\CommandBus;

use HelloFresh\Engine\CommandBus\CommandBusInterface;
use HelloFresh\Engine\CommandBus\EventDispatchingCommandBus;
use HelloFresh\Engine\CommandBus\Exception\MissingHandlerException;
use HelloFresh\Engine\CommandBus\Handler\InMemoryLocator;
use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Engine\EventDispatcher\EventDispatcher;
use HelloFresh\Tests\Engine\Mock\InvalidHandler;
use HelloFresh\Tests\Engine\Mock\TestCommand;
use HelloFresh\Tests\Engine\Mock\TestHandler;

class EventDispatchingCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryLocator
     */
    private $locator;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    protected function setUp()
    {
        $this->locator = new InMemoryLocator();
        $simpleCommandBus = new SimpleCommandBus($this->locator);
        $eventDispatcher = new EventDispatcher();
        $this->commandBus = new EventDispatchingCommandBus($simpleCommandBus, $eventDispatcher);
    }

    /**
     * @test
     */
    public function itExecutesAMessage()
    {
        $handler = new TestHandler();
        $this->locator->addHandler(TestCommand::class, $handler);

        $command = new TestCommand("hey");
        $this->commandBus->execute($command);
        $this->commandBus->execute($command);
        $this->commandBus->execute($command);

        $this->assertSame(3, $handler->getCounter());
    }

    /**
     * @test
     * @expectedException \HelloFresh\Engine\CommandBus\Exception\MissingHandlerException
     */
    public function itLosesMessageWhenThereIsNoHandlers()
    {
        $command = new TestCommand("hey");
        $this->commandBus->execute($command);

        $handler = new TestHandler();
        $this->assertSame(0, $handler->getCounter());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itFailsWhenHaveInvalidSubscriber()
    {
        $command = new TestCommand("hey");
        $handler = new TestHandler();

        $this->locator->addHandler($command, $handler);
        $this->commandBus->execute($command);
    }

    /**
     * @test
     * @expectedException \HelloFresh\Engine\CommandBus\Exception\CanNotInvokeHandlerException
     */
    public function itFailsWhenHandlerHasAnInvalidHandleMethod()
    {
        $handler = new InvalidHandler();
        $this->locator->addHandler(TestCommand::class, $handler);

        $command = new TestCommand("hey");
        $this->commandBus->execute($command);
    }
}
