<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\EventStore\Exception\EventStreamNotFoundException;
use HelloFresh\Engine\Serializer\SerializerInterface;
use MongoDB\Client;
use MongoDB\Collection;

class MongoDbAdapter implements EventStoreAdapterInterface
{
    const COLLECTION_NAME = 'events';
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(Client $client, $dbName, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->dbName = $dbName;
        $this->serializer = $serializer;
    }

    public function save(DomainMessage $event)
    {
        $data = [
            'aggregate_id' => (string)$event->getId(),
            'version' => $event->getVersion(),
            'type' => $event->getType(),
            'payload' => $this->serializer->serialize($event->getPayload(), 'json'),
            'recorded_on' => $event->getRecordedOn()->format('Y-m-d\TH:i:s.u'),
        ];

        $this->getCollection()->insertOne($data);
    }

    public function getEventsFor($id)
    {
        $query['aggregate_id'] = (string)$id;

        $collection = $this->getCollection();
        $serializedEvents = $collection->find($query, ['sort' => ['version' => 1]]);

        if (!$serializedEvents) {
            throw new EventStreamNotFoundException($id);
        }

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        $query['aggregate_id'] = (string)$aggregateId;

        if (null !== $version) {
            $query['version'] = ['$gte' => $version];
        }

        $collection = $this->getCollection();
        $serializedEvents = $collection->find($query, ['sort' => ['version' => 1]]);

        if (!$serializedEvents) {
            throw new EventStreamNotFoundException($aggregateId);
        }

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        $query['aggregate_id'] = (string)$aggregateId;
        $collection = $this->getCollection();

        return $collection->count($query);
    }

    private function processEvents($serializedEvents)
    {
        $eventStream = [];

        foreach ($serializedEvents as $eventData) {
            $payload = $this->serializer->deserialize($eventData['payload'], $eventData['type'], 'json');

            $eventStream[] = new DomainMessage(
                $eventData['aggregate_id'],
                $eventData['version'],
                $payload,
                \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u', $eventData['recorded_on'])
            );
        }

        return $eventStream;
    }

    /**
     * Get mongo db stream collection
     *
     * @return Collection
     */
    private function getCollection()
    {
        return $this->client->selectCollection($this->dbName, static::COLLECTION_NAME);
    }
}
