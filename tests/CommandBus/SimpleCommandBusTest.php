<?php

namespace HelloFresh\Tests\Engine\CommandBus;

use HelloFresh\Engine\CommandBus\CommandBusInterface;
use HelloFresh\Engine\CommandBus\Handler\InMemoryLocator;
use HelloFresh\Engine\CommandBus\SimpleCommandBus;
use HelloFresh\Tests\Engine\Mock\InvalidHandler;
use HelloFresh\Tests\Engine\Mock\TestCommand;
use HelloFresh\Tests\Engine\Mock\TestHandler;

class SimpleCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var InMemoryLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->locator = new InMemoryLocator();
        $this->commandBus = new SimpleCommandBus($this->locator);
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
    public function itFailsWhenThereIsNoHandlers()
    {
        $command = new TestCommand("hey");
        $this->commandBus->execute($command);
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
