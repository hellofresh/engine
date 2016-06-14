<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use Doctrine\DBAL\Connection;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Domain\StreamName;
use HelloFresh\Engine\Serializer\SerializerInterface;

class DbalAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function save(StreamName $streamName, DomainMessage $event)
    {
        $data = $this->createEventData($event);
        $this->connection->insert($streamName, $data);
    }

    public function getEventsFor(StreamName $streamName, $id)
    {
        $queryBuilder = $this->getQueryBuilder($streamName);
        $queryBuilder->where('aggregate_id = :id')
            ->addOrderBy('version')
            ->setParameter('id', (string)$id);

        $serializedEvents = $queryBuilder->execute();

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(StreamName $streamName, AggregateIdInterface $aggregateId, $version)
    {
        $queryBuilder = $this->getQueryBuilder($streamName);
        $queryBuilder->where('aggregate_id = :id')
            ->andWhere('version >= :version')
            ->addOrderBy('version')
            ->setParameter('id', (string)$aggregateId)
            ->setParameter('version', $version);

        $serializedEvents = $queryBuilder->execute();

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(StreamName $streamName, AggregateIdInterface $aggregateId)
    {
        $queryBuilder = $this->getQueryBuilder($streamName)
            ->select('count(aggregate_id)')
            ->where('aggregate_id = :id')
            ->setParameter('id', (string)$aggregateId);

        return $queryBuilder->execute()->fetch()["count"];
    }

    private function getQueryBuilder(StreamName $streamName)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder
            ->select('*')
            ->from((string)$streamName);
    }
}
