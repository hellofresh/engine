<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter;

use HelloFresh\Engine\Domain\AggregateId;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\Serializer\SerializerInterface;

trait SnapshotProcessorTrait
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    private function createEventData(Snapshot $snapshot)
    {
        return [
            'aggregate_id' => $snapshot->getAggregateId(),
            'version' => $snapshot->getVersion(),
            'created_at' => $snapshot->getCreatedAt()->getTimestamp(),
            'type' => $snapshot->getType(),
            'payload' => $this->serializer->serialize($snapshot->getAggregate(), 'json')
        ];
    }

    private function processSnapshot($metadata)
    {
        if (false === $metadata) {
            return null;
        }

        /** @var AggregateRootInterface $aggregate */
        $aggregate = $this->serializer->deserialize(
            $metadata['payload'],
            $metadata['type'],
            'json'
        );

        return new Snapshot(
            AggregateId::fromString($metadata['aggregate_id']),
            $aggregate,
            $metadata['version'],
            new \DateTimeImmutable("@" . $metadata['created_at'])
        );
    }
}
