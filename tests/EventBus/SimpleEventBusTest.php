<?php

namespace HelloFresh\Tests\Engine\EventBus;

use HelloFresh\Engine\EventBus\EventBusInterface;
use HelloFresh\Engine\EventBus\SimpleEventBus;
use HelloFresh\Tests\Engine\Mock\AllEventsListener;
use HelloFresh\Tests\Engine\Mock\SomethingDone;
use HelloFresh\Tests\Engine\Mock\SomethingHappened;
use HelloFresh\Tests\Engine\Mock\SomethingHappenedListener;

class SimpleEventBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventBusInterface
     */
    private $eventBus;

    protected function setUp()
    {
        $this->eventBus = new SimpleEventBus();
    }

    /**
     * @test
     */
    public function itListensToAMessage()
    {
        $listener = new SomethingHappenedListener();
        $this->eventBus->subscribe($listener);

        $event = new SomethingHappened();
        $this->eventBus->publish($event);
        $this->eventBus->publish($event);
        $this->eventBus->publish($event);

        $this->assertSame(3, $listener->getCounter());
    }

    /**
     * @test
     */
    public function itListensToAllEvents()
    {
        $listener = new AllEventsListener();
        $this->eventBus->subscribe($listener);

        $event1 = new SomethingHappened();
        $event2 = new SomethingDone();
        $this->eventBus->publish($event1);
        $this->eventBus->publish($event2);
        $this->eventBus->publish($event1);
        $this->eventBus->publish($event2);

        $this->assertSame(4, $listener->getCounter());
    }


    /**
     * @test
     */
    public function itLosesMessageWhenThereIsNoHandlers()
    {
        $listener = new SomethingHappenedListener();

        $event = new SomethingHappened();
        $this->eventBus->publish($event);
        $this->eventBus->publish($event);
        $this->eventBus->publish($event);

        $this->assertSame(0, $listener->getCounter());
    }

}
