<?php

namespace HelloFresh\Tests\Engine\EventStore\Aggregate;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use Ramsey\Uuid\Uuid;

class AggregateIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateAUuidFromConstructor()
    {
        $aggregateId = new AggregateId(Uuid::uuid4());
        $this->assertInstanceOf(AggregateIdInterface::class, $aggregateId);
    }

    /**
     * @test
     */
    public function itShouldCreateAUuidFromNamedConstructor()
    {
        $aggregateId = AggregateId::generate();
        $this->assertInstanceOf(AggregateIdInterface::class, $aggregateId);
    }

    /**
     * @test
     */
    public function itShouldCreateAUuidFromAString()
    {
        $aggregateIdString = Uuid::uuid4();
        $aggregateId = AggregateId::fromString($aggregateIdString);

        $this->assertInstanceOf(AggregateIdInterface::class, $aggregateId);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldFailWhenCreatingAnIdFromInvalidString()
    {
        $aggregateIdString = 'invalidUuid';
        $aggregateId = AggregateId::fromString($aggregateIdString);

        $this->assertInstanceOf(AggregateIdInterface::class, $aggregateId);
    }
}
