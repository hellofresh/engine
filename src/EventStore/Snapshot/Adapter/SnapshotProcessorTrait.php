<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\EventStore\Exception\EventStreamNotFoundException;
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
        if (!$metadata) {
            throw new EventStreamNotFoundException('The snapshot doesn\'t exists');
        }

        /** @var AggregateRootInterface $aggregate */
        $aggregate = $this->serializer->deserialize(
            $metadata['payload'],
            $metadata['type'],
            'json'
        );

        return new Snapshot(
            $metadata['aggregate_id'],
            $aggregate,
            $metadata['version'],
            new \DateTimeImmutable("@" . $metadata['created_at'])
        );
    }
}
