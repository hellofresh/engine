<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\EventStore\Exception\EventStreamNotFoundException;
use HelloFresh\Engine\Serializer\SerializerInterface;
use Predis\ClientInterface;

class RedisAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

    /**
     * @var ClientInterface
     */
    private $redis;

    public function __construct(ClientInterface $redis, SerializerInterface $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    public function save(StreamName $streamName, DomainMessage $event)
    {
        $data = $this->serializer->serialize($this->createEventData($event), 'json');

        $this->redis->lpush($this->getNamespaceKey($streamName, $event->getId()), $data);
        $this->redis->rpush('published_events', $data);
    }

    public function getEventsFor(StreamName $streamName, $id)
    {
        if (!$this->redis->exists($this->getNamespaceKey($streamName, $id))) {
            throw new EventStreamNotFoundException($id);
        }

        $serializedEvents = $this->redis->lrange('events:' . $id, 0, -1);

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version)
    {
        if (!$this->redis->exists($this->getNamespaceKey($streamName, $aggregateId))) {
            throw new EventStreamNotFoundException($aggregateId);
        }

        $serializedEvents = $this->redis->lrange('events:' . $aggregateId, 0, $version);

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId)
    {
        return count($this->redis->lrange($this->getNamespaceKey($streamName, $aggregateId), 0, -1));
    }

    private function getNamespaceKey(StreamName $streamName, AggregateIdInterface $aggregateId)
    {
        return "events:{$streamName}:{$aggregateId}";
    }
}
