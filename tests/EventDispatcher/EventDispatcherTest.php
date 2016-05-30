<?php

namespace HelloFresh\Tests\Engine\EventBus;

use HelloFresh\Engine\EventDispatcher\EventDispatcher;
use HelloFresh\Engine\EventDispatcher\EventDispatcherInterface;
use HelloFresh\Tests\Engine\Mock\TracableEventListener;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    private $listener1;
    private $listener2;

    public function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->listener1 = new TracableEventListener();
        $this->listener2 = new TracableEventListener();
        $this->assertFalse($this->listener1->isCalled());
        $this->assertFalse($this->listener2->isCalled());
    }

    /**
     * @test
     */
    public function itCallsSubscribedListeners()
    {
        $this->dispatcher->addListener('event', [$this->listener1, 'handleEvent']);
        $this->dispatcher->addListener('event', [$this->listener2, 'handleEvent']);
        $this->dispatcher->dispatch('event', 'value1', 'value2');
        $this->assertTrue($this->listener1->isCalled());
        $this->assertTrue($this->listener2->isCalled());
    }

    /**
     * @test
     */
    public function itOnlyCallsTheListenerSubscribedToAGivenEvent()
    {
        $this->dispatcher->addListener('event1', [$this->listener1, 'handleEvent']);
        $this->dispatcher->addListener('event2', [$this->listener2, 'handleEvent']);
        $this->dispatcher->dispatch('event1', 'value1', 'value2');
        $this->assertTrue($this->listener1->isCalled());
        $this->assertFalse($this->listener2->isCalled());
    }
}
