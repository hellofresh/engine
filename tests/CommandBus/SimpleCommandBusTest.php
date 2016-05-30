<?php

namespace HelloFresh\Tests\Engine\CommandBus;

use HelloFresh\Engine\CommandBus\CommandBusInterface;
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

    protected function setUp()
    {
        $this->commandBus = new SimpleCommandBus();
    }

    /**
     * @test
     */
    public function itExecutesAMessage()
    {
        $handler = new TestHandler();
        $this->commandBus->subscribe(TestCommand::class, $handler);

        $command = new TestCommand("hey");
        $this->commandBus->execute($command);
        $this->commandBus->execute($command);
        $this->commandBus->execute($command);

        $this->assertSame(3, $handler->getCounter());
    }

    /**
     * @test
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
     * @expectedException \Assert\InvalidArgumentException
     */
    public function itFailsWhenHaveInvalidSubscriber()
    {
        $command = new TestCommand("hey");
        $handler = new TestHandler();

        $this->commandBus->subscribe($command, $handler);
        $this->commandBus->execute($command);
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     */
    public function itFailsWhenHandlerHasAnInvalidHandleMethod()
    {
        $handler = new InvalidHandler();
        $this->commandBus->subscribe(TestCommand::class, $handler);

        $command = new TestCommand("hey");
        $this->commandBus->execute($command);
    }
}
