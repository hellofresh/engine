<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\Serializer\SerializerInterface;
use Predis\ClientInterface;

class RedisSnapshotAdapter implements SnapshotStoreAdapterInterface
{
    const KEY_NAMESPACE = 'snapshots';

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

    /**
     * @inheritdoc
     */
    public function byId(AggregateIdInterface $id)
    {
        if (!$this->redis->hexists(static::KEY_NAMESPACE, (string)$id)) {
            return null;
        }

        $metadata = $this->serializer->deserialize(
            $this->redis->hget(static::KEY_NAMESPACE, (string)$id),
            'array',
            'json'
        );

        if (!is_array($metadata)) {
            return null;
        }

        /** @var AggregateRootInterface $aggregate */
        $aggregate = $this->serializer->deserialize(
            $metadata['snapshot']['payload'],
            $metadata['snapshot']['type'],
            'json'
        );

        $createdAt = \DateTimeImmutable::createFromFormat('U.u', $metadata['created_at']);
        $createdAt->setTimezone(new \DateTimeZone('UTC'));

        return new Snapshot(
            $aggregate->getAggregateRootId(),
            $aggregate,
            $metadata['version'],
            $createdAt
        );
    }

    /**
     * @inheritdoc
     */
    public function save(Snapshot $snapshot)
    {
        $data = [
            'version' => $snapshot->getVersion(),
            'created_at' => $snapshot->getCreatedAt()->format('U.u'),
            'snapshot' => [
                'type' => $snapshot->getType(),
                'payload' => $this->serializer->serialize($snapshot->getAggregate(), 'json')
            ]
        ];

        $this->redis->hset(
            static::KEY_NAMESPACE,
            (string)$snapshot->getAggregateId(),
            $this->serializer->serialize($data, 'json')
        );
    }


    /**
     * @inheritdoc
     */
    public function has(AggregateIdInterface $id, $version)
    {
        if (!$this->redis->hexists(static::KEY_NAMESPACE, (string)$id)) {
            return false;
        }

        $snapshot = $this->serializer->deserialize(
            $this->redis->hget(static::KEY_NAMESPACE, (string)$id),
            'array',
            'json'
        );

        return $snapshot['version'] === $version;
    }
}
