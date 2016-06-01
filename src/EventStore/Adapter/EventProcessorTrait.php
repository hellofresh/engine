<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\EventStore\Exception\EventStreamNotFoundException;
use HelloFresh\Engine\Serializer\SerializerInterface;

trait EventProcessorTrait
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    private function createEventData(DomainMessage $event)
    {
        return [
            'aggregate_id' => (string)$event->getId(),
            'version' => $event->getVersion(),
            'type' => $event->getType(),
            'payload' => $this->serializer->serialize($event->getPayload(), 'json'),
            'recorded_on' => $event->getRecordedOn()->format('Y-m-d\TH:i:s.u'),
        ];
    }

    private function processEvents($serializedEvents)
    {
        if (!$serializedEvents) {
            throw new EventStreamNotFoundException('The event stream doesn\'t exists');
        }

        $eventStream = [];

        foreach ($serializedEvents as $eventData) {
            if (is_string($eventData)) {
                $eventData = $this->serializer->deserialize($eventData, 'array', 'json');
            }

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
}
