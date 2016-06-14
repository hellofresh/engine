<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Serializer\SerializerInterface;

class MongoAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

    /**
     * @var \MongoClient
     */
    private $client;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var string
     */
    private $collectionName;

    public function __construct(
        \MongoClient $client,
        SerializerInterface $serializer,
        $dbName,
        $collectionName = 'events'
    ) {
        $this->client = $client;
        $this->dbName = $dbName;
        $this->collectionName = $collectionName;
        $this->serializer = $serializer;

        $this->createIndexes();
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
        $serializedEvents = $collection->find($query)->sort(['version' => \MongoCollection::ASCENDING]);

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        $query['aggregate_id'] = (string)$aggregateId;

        if (null !== $version) {
            $query['version'] = ['$gte' => $version];
        }

        $collection = $this->getCollection();
        $serializedEvents = $collection->find($query)->sort(['version' => \MongoCollection::ASCENDING]);;

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        $query['aggregate_id'] = (string)$aggregateId;
        $collection = $this->getCollection();

        return $collection->count($query);
    }

    /**
     * Get mongo db stream collection
     *
     * @return \MongoCollection
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
