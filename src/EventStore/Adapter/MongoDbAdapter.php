<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Serializer\SerializerInterface;
use MongoDB\Client;
use MongoDB\Collection;

class MongoDbAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

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

    /**
     * @var string
     */
    private $collectionName;

    public function __construct(
        Client $client,
        SerializerInterface $serializer,
        $dbName,
        $collectionName = 'events'
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->dbName = $dbName;
        $this->collectionName = $collectionName;

        $this->createIndexes();
    }

    public function save(DomainMessage $event)
    {
        $data = $this->createEventData($event);
        $this->getCollection()->insertOne($data);
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
     * @return Collection
     */
    private function getCollection()
    {
        return $this->client->selectCollection($this->dbName, $this->collectionName);
    }

    /**
     * @return void
     */
    private function createIndexes()
    {
        $collection = $this->getCollection();
        $collection->createIndex(
            [
                'aggregate_id' => 1,
                'version' => 1,
            ],
            [
                'unique' => true,
            ]
        );
    }
}
