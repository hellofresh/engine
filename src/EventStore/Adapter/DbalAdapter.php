<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use Doctrine\DBAL\Connection;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Serializer\SerializerInterface;

class DbalAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    public function __construct(Connection $connection, SerializerInterface $serializer, $tableName)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->tableName = $tableName;
    }

    public function save(DomainMessage $event)
    {
        $data = $this->createEventData($event);
        $this->connection->insert($this->tableName, $data);
    }

    public function getEventsFor($id)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where('aggregate_id = :id')
            ->addOrderBy('version')
            ->setParameter('id', (string)$id);

        $serializedEvents = $queryBuilder->execute();

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where('aggregate_id = :id')
            ->andWhere('version >= :version')
            ->addOrderBy('version')
            ->setParameter('id', (string)$aggregateId)
            ->setParameter('version', $version);

        $serializedEvents = $queryBuilder->execute();

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        $queryBuilder = $this->getQueryBuilder()
            ->select('count(aggregate_id)')
            ->where('aggregate_id = :id')
            ->setParameter('id', (string)$aggregateId);

        return $queryBuilder->execute()->fetch()["count"];
    }

    private function getQueryBuilder()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder
            ->select('*')
            ->from($this->tableName);
    }
}
