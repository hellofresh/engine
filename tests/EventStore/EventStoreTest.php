<?php

namespace HelloFresh\Tests\Engine\EventStore;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\EventStream;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\EventStore\EventStoreInterface;
use HelloFresh\Tests\Engine\Mock\SomethingHappened;
use Ramsey\Uuid\Uuid;

abstract class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventStoreInterface
     */
    protected $eventStore;

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_creates_a_new_entry_when_id_is_new($id)
    {
        $domainEventStream = new EventStream(new StreamName('event_stream'), [
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ]);

        $this->eventStore->append($domainEventStream);
        $this->assertEquals($domainEventStream, $this->eventStore->getEventsFor(new StreamName('event_stream'), $id));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_appends_to_an_already_existing_stream($id)
    {
        $dateTime = new \DateTimeImmutable("now");

        $domainEventStream = new EventStream(new StreamName('event_stream'), [
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
        ]);
        $this->eventStore->append($domainEventStream);
        $appendedEventStream = new EventStream(new StreamName('event_stream'), [
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ]);
        $this->eventStore->append($appendedEventStream);
        $expected = new EventStream(new StreamName('event_stream'), [
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ]);
        $this->assertEquals($expected, $this->eventStore->getEventsFor(new StreamName('event_stream'), $id));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException \HelloFresh\Engine\EventStore\Exception\EventStreamNotFoundException
     */
    public function it_throws_an_exception_when_requesting_the_stream_of_a_non_existing_aggregate($id)
    {
        $this->eventStore->getEventsFor(new StreamName('event_stream'), $id);
    }

    public function idDataProvider()
    {
        return [
            'Simple String' => [
                'Yolntbyaac', // You only live nine times because you are a cat
            ],
            'Identitiy' => [
                AggregateId::generate(),
            ],
            'UUID String' => [
                Uuid::uuid4()->toString(), // test UUID
            ],
        ];
    }

    protected function createDomainMessage($id, $version, $recordedOn = null)
    {
        return new DomainMessage(
            $id,
            $version,
            new SomethingHappened($recordedOn),
            $recordedOn ? $recordedOn : new \DateTimeImmutable("now")
        );
    }
}
