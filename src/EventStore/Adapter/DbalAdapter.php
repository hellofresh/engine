<?php

namespace HelloFresh\Engine\EventStore\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\DomainMessage;
use HelloFresh\Engine\Serializer\SerializerInterface;

class DbalAdapter implements EventStoreAdapterInterface
{
    use EventProcessorTrait;

    const TABLE_NAME = 'events';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbName;


    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function save(DomainMessage $event)
    {
        $data = $this->createEventData($event);
        $this->connection->insert(static::TABLE_NAME, $data);
    }

    public function getEventsFor($id)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where('e.aggregate_id = :id')
            ->setParameter('id', (string)$id);

        $serializedEvents = $queryBuilder->execute();

        return $this->processEvents($serializedEvents);
    }

    public function fromVersion(AggregateIdInterface $aggregateId, $version)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where('e.aggregate_id = :id')
            ->andWhere('e.version = :version')
            ->setParameter('id', (string)$aggregateId)
            ->setParameter('version', $version);

        $serializedEvents = $queryBuilder->execute();

        return $this->processEvents($serializedEvents);
    }

    public function countEventsFor(AggregateIdInterface $aggregateId)
    {
        $queryBuilder = $this->getQueryBuilder()
            ->select('count(aggregate_id)')
            ->where('e.aggregate_id = :id')
            ->setParameter('id', (string)$aggregateId);

        return $queryBuilder->execute();
    }

    private function getQueryBuilder()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder
            ->select('*')
            ->from(static::TABLE_NAME, 'e')
            ->orderBy('version', 'ASC');
    }

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function createSchema(Connection $connection)
    {
        $schema = new Schema();
        static::addToSchema($schema, static::TABLE_NAME);
        $sqls = $schema->toSql($connection->getDatabasePlatform());

        foreach ($sqls as $sql) {
            $connection->executeQuery($sql);
        }
    }

    /**
     * @param Schema $schema
     * @param string $table
     */
    public static function addToSchema(Schema $schema, $table)
    {
        $table = $schema->createTable($table);
        $table->addColumn('aggregate_id', 'string', ['length' => 50]);
        $table->addColumn('version', 'integer');
        $table->addColumn('type', 'string', ['length' => 100]);
        $table->addColumn('payload', 'text');
        $table->addColumn('recorded_on', 'string', ['length' => 50]);

        $table->setPrimaryKey(['event_id']);
    }
}
