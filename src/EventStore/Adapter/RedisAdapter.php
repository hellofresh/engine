<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
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

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(ClientInterface $redis, SerializerInterface $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    public function save(DomainMessage $event)
    {
        $data = $this->serializer->serialize($this->createEventData($event), 'json');

        $this->redis->lpush('events:' . $event->getId(), $data);
        $this->redis->rpush('published_events', $data);
    }

    public function getEventsFor($id)
    {
        if (!$this->redis->exists('events:' . $id)) {
            throw new EventStreamNotFoundException($id);
        }

        $serializedEvents = $this->redis->lrange('events:' . $id, 0, -1);

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        if (!$this->redis->exists('events:' . (string)$aggregateId)) {
            throw new EventStreamNotFoundException($aggregateId);
        }

        $serializedEvents = $this->redis->lrange('events:' . $aggregateId, 0, $version);

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        return count($this->redis->lrange('events:' . $aggregateId, 0, -1));
    }
}