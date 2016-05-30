<?php

namespace HelloFresh\Tests\Engine\EventSourcing;

use HelloFresh\Engine\EventSourcing\AggregateRepositoryFactory;
use HelloFresh\Engine\EventSourcing\EventSourcingRepository;
use HelloFresh\Engine\EventStore\Adapter\RedisAdapter;
use Prophecy\Argument;

class AggregateRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateADefaultRepository()
    {
        $factory = new AggregateRepositoryFactory();
        $repo = $factory->build();
        $this->assertInstanceOf(EventSourcingRepository::class, $repo);
    }

    /**
     * @test
     */
    public function itShouldCreateARepositoryOnlyWithEventStore()
    {
        $factory = new AggregateRepositoryFactory([
            'event_store' => [
                'adapter' => RedisAdapter::class
            ]
        ]);
        $repo = $factory->build();
        $this->assertInstanceOf(EventSourcingRepository::class, $repo);
    }
}
