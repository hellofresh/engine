<?php

namespace HelloFresh\Tests\Engine\CommandBus;

use Assert\InvalidArgumentException;
use HelloFresh\Engine\CommandBus\CommandBusInterface;
use HelloFresh\Engine\CommandBus\TacticianCommandBus;
use HelloFresh\Tests\Engine\Mock\InvalidHandler;
use HelloFresh\Tests\Engine\Mock\TestCommand;
use HelloFresh\Tests\Engine\Mock\TestHandler;
use League\Tactician\Exception\CanNotInvokeHandlerException;
use League\Tactician\Exception\MissingHandlerException;

class TacticianCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    protected function setUp()
    {
        $this->commandBus = new TacticianCommandBus();
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
    public function itFailsWhenThereIsNoHandlers()
    {
        $command = new TestCommand("hey");
        
        $this->expectException(MissingHandlerException::class);
        
        $this->commandBus->execute($command);
    }

    /**
     * @test
     */
    public function itFailsWhenHaveInvalidSubscriber()
    {
        $command = new TestCommand("hey");
        $handler = new TestHandler();

        $this->expectException(InvalidArgumentException::class);

        $this->commandBus->subscribe($command, $handler);
    }

    /**
     * @test
     */
    public function itFailsWhenHandlerHasAnInvalidHandleMethod()
    {
        $command = new TestCommand("hey");
        $handler = new InvalidHandler();

        $this->commandBus->subscribe(TestCommand::class, $handler);

        $this->expectException(CanNotInvokeHandlerException::class);

        $this->commandBus->execute($command);
    }
}
