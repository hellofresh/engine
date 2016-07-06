<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter;

use Doctrine\DBAL\Connection;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\EventStore\Snapshot\Snapshot;
use HelloFresh\Engine\Serializer\SerializerInterface;

class DbalSnapshotAdapter implements SnapshotStoreAdapterInterface
{
    use SnapshotProcessorTrait;

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

    /**
     * @inheritdoc
     */
    public function byId(AggregateIdInterface $id)
    {
        $queryBuilder = $this->getQueryBuilder()
            ->where('aggregate_id = :id')
            ->addOrderBy('version')
            ->setMaxResults(1)
            ->setParameter('id', (string)$id);

        $snapshot = $queryBuilder->execute()->fetch();

        return $this->processSnapshot($snapshot);
    }

    /**
     * @inheritdoc
     */
    public function save(Snapshot $snapshot)
    {
        $data = $this->createEventData($snapshot);
        $this->connection->insert($this->tableName, $data);
    }


    /**
     * @inheritdoc
     */
    public function has(AggregateIdInterface $id, $version)
    {
        $queryBuilder = $this->getQueryBuilder()
            ->where('aggregate_id = :id')
            ->setMaxResults(1)
            ->addOrderBy('version')
            ->setParameter('id', (string)$id);

        $metadata = $queryBuilder->execute()->fetch();

        if (empty($metadata)) {
            return false;
        }

        return $metadata['version'] === $version;
    }

    private function getQueryBuilder()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder
            ->select('*')
            ->from($this->tableName);
    }
}
