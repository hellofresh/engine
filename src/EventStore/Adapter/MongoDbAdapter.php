<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\StreamName;
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

    public function __construct(
        Client $client,
        SerializerInterface $serializer,
        $dbName
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->dbName = $dbName;
    }

    public function save(StreamName $streamName, DomainMessage $event)
    {
        $this->createIndexes($streamName);
        $data = $this->createEventData($event);
        $this->getCollection($streamName)->insertOne($data);
    }

    public function getEventsFor(StreamName $streamName, $id)
    {
        $query['aggregate_id'] = (string)$id;

        $collection = $this->getCollection($streamName);
        $serializedEvents = $collection->find($query, ['sort' => ['version' => 1]]);

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version)
    {
        $query['aggregate_id'] = (string)$aggregateId;

        if (null !== $version) {
            $query['version'] = ['$gte' => $version];
        }

        $collection = $this->getCollection($streamName);
        $serializedEvents = $collection->find($query, ['sort' => ['version' => 1]]);

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId)
    {
        $query['aggregate_id'] = (string)$aggregateId;
        $collection = $this->getCollection($streamName);

        return $collection->count($query);
    }

    /**
     * Get mongo db stream collection
     *
     * @param StreamName $streamName
     * @return Collection
     */
    private function getCollection(StreamName $streamName)
    {
        return $this->client->selectCollection($this->dbName, (string)$streamName);
    }

    /**
     * @param StreamName $streamName
     */
    private function createIndexes(StreamName $streamName)
    {
        $collection = $this->getCollection($streamName);
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
