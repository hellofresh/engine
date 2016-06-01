<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Serializer\SerializerInterface;

class MongoAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

    const COLLECTION_NAME = 'events';
    /**
     * @var \MongoClient
     */
    private $client;

    /**
     * @var string
     */
    private $dbName;

    public function __construct(\MongoClient $client, $dbName, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->dbName = $dbName;
        $this->serializer = $serializer;
    }

    public function save(DomainMessage $event)
    {
        $data = $this->createEventData($event);

        $this->getCollection()->insert($data);
    }

    public function getEventsFor($id)
    {
        $query['aggregate_id'] = (string)$id;

        $collection = $this->getCollection();
        $serializedEvents = $collection->find($query, ['sort' => ['version' => 1]]);

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
     * @return \MongoCollection
     */
    private function getCollection()
    {
        return $this->client->selectCollection($this->dbName, static::COLLECTION_NAME);
    }
}
