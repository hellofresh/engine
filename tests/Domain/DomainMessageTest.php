<?php

namespace HelloFresh\Tests\Engine\Domain;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Tests\Engine\Mock\SomethingHappened;

class DomainMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider messageProvider
     * @param AggregateIdInterface $aggregateId
     * @param $version
     * @param $payload
     * @param \DateTimeImmutable $date
     */
    public function itShouldCreateAUuidFromConstructor(
        AggregateIdInterface $aggregateId,
        $version,
        $payload,
        \DateTimeImmutable $date
    ) {
        $message = new DomainMessage($aggregateId, $version, $payload, $date);
        $this->assertInstanceOf(DomainMessage::class, $message);
        $this->assertSame($aggregateId, $message->getId());
        $this->assertSame($version, $message->getVersion());
        $this->assertSame($payload, $message->getPayload());
        $this->assertEquals($date, $message->getRecordedOn());
        $this->assertEquals(new \DateTimeZone('UTC'), $message->getRecordedOn()->getTimezone());
    }

    /**
     * @test
     * @dataProvider messageProvider
     * @param AggregateIdInterface $aggregateId
     * @param $version
     * @param $payload
     * @param \DateTimeImmutable $date
     */
    public function itShouldCreateAUuidFromNamedConstructor(
        AggregateIdInterface $aggregateId,
        $version,
        $payload,
        \DateTimeImmutable $date
    ) {
        $message = DomainMessage::recordNow($aggregateId, $version, $payload);
        $this->assertInstanceOf(DomainMessage::class, $message);

        $this->assertNotEmpty((int)$message->getRecordedOn()->format('u'), 'Expected microseconds to be set');
        $this->assertEquals(new \DateTimeZone('UTC'), $message->getRecordedOn()->getTimezone());
    }

    public function messageProvider()
    {
        return [
            [AggregateId::generate(), 1, new SomethingHappened(), new \DateTimeImmutable()],
            [AggregateId::generate(), 100, new SomethingHappened(), new \DateTimeImmutable()],
            [AggregateId::generate(), 9999999, new SomethingHappened(), new \DateTimeImmutable()],
            [
                AggregateId::generate(),
                9999999,
                new SomethingHappened(),
                \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)))
            ]
        ];
    }
}
