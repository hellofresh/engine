<?php

namespace HelloFresh\Tests\Engine\EventStore\Snapshot;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Tests\Engine\Mock\AggregateRoot;
use PHPUnit\Framework\TestCase;

class SnapshotTest extends TestCase
{
    /**
     * @test
     * @dataProvider messageProvider
     * @param AggregateIdInterface $aggregateId
     * @param AggregateRootInterface $aggregate
     * @param $version
     * @param \DateTimeImmutable $date
     * @internal param $payload
     */
    public function itShouldCreateASnapshotFromConstructor(
        AggregateIdInterface $aggregateId,
        AggregateRootInterface $aggregate,
        $version,
        \DateTimeImmutable $date
    ) {
        $message = new Snapshot($aggregateId, $aggregate, $version, $date);

        $this->assertInstanceOf(Snapshot::class, $message);
        $this->assertSame($aggregateId, $message->getAggregateId());
        $this->assertSame($aggregate, $message->getAggregate());
        $this->assertSame($version, $message->getVersion());
        $this->assertSame(get_class($aggregate), $message->getType());
        $this->assertEquals($date, $message->getCreatedAt());
        $this->assertEquals(new \DateTimeZone('UTC'), $message->getCreatedAt()->getTimezone());
    }

    /**
     * @test
     * @dataProvider messageProvider
     * @param AggregateIdInterface $aggregateId
     * @param AggregateRootInterface $aggregate
     * @param string $version
     */
    public function itShouldCreateASnapshotFromNamedConstructor(
        AggregateIdInterface $aggregateId,
        AggregateRootInterface $aggregate,
        $version
    ) {
        $snapshot = Snapshot::take($aggregateId, $aggregate, $version);

        $this->assertInstanceOf(Snapshot::class, $snapshot);
        $this->assertNotEmpty((int)$snapshot->getCreatedAt()->format('u'), 'Expected microseconds to be set');
        $this->assertEquals(new \DateTimeZone('UTC'), $snapshot->getCreatedAt()->getTimezone());
    }

    public function messageProvider()
    {
        return [
            [
                AggregateId::generate(),
                AggregateRoot::create(AggregateId::generate(), 'v1000'),
                '1000',
                new \DateTimeImmutable()
            ],
            [
                AggregateId::generate(),
                AggregateRoot::create(AggregateId::generate(), 'v1'),
                '1',
                \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)))
            ]
        ];
    }
}
