<?php

namespace HelloFresh\Tests\Engine\CommandBus;

use HelloFresh\Engine\CommandBus\TacticianCommandBus;
use HelloFresh\Tests\Engine\Mock\InvalidHandler;
use HelloFresh\Tests\Engine\Mock\TestCommand;
use HelloFresh\Tests\Engine\Mock\TestHandler;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;

class TacticianCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryLocator
     */
    private $locator;
    /**
     * @var CommandBus
     */
    private $internalCommandBus;
    /**
     * @var TacticianCommandBus
     */
    private $commandBus;

    protected function setUp()
    {
        $this->locator = new InMemoryLocator();
        $this->internalCommandBus = self::createASimpleBus($this->locator);

        $this->commandBus = new TacticianCommandBus($this->internalCommandBus);
    }

    /**
     * @test
     */
    public function itExecutesAMessage()
    {
        $handler = new TestHandler();
        $this->locator->addHandler($handler, TestCommand::class);

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

        $handler = new TestHandler();
        $this->assertSame(0, $handler->getCounter());
    }

    /**
     * @test
     * @expectedException \HelloFresh\Engine\CommandBus\Exception\CanNotInvokeHandlerException
     */
    public function itFailsWhenHandlerHasAnInvalidHandleMethod()
    {
        $handler = new InvalidHandler();
        $this->locator->addHandler($handler, TestCommand::class);

        $command = new TestCommand("hey");
        $this->commandBus->execute($command);
    }

    /**
     * Create a tactician command bus that uses the same convention as SimpleCommandBus.
     *
     * @param HandlerLocator $commandLocator
     * @return CommandBus
     */
    public static function createASimpleBus(HandlerLocator $commandLocator)
    {
        return new CommandBus([
            new LockingMiddleware(),
            new CommandHandlerMiddleware(
                new ClassNameExtractor(),
                $commandLocator,
                new HandleInflector()
            )
        ]);
    }
}
