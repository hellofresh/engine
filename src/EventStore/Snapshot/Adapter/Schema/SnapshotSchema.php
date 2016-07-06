<?php

namespace HelloFresh\Engine\EventStore\Snapshot\Adapter\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class SnapshotSchema
{
    const TABLE_NAME = 'snapshots';

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function createSchema(Connection $connection)
    {
        $sm = $connection->getSchemaManager();
        $fromSchema = $sm->createSchema();

        $toSchema = clone $fromSchema;
        static::addToSchema($toSchema, static::TABLE_NAME);


        $sqls = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());

        foreach ($sqls as $sql) {
            $connection->executeQuery($sql);
        }
    }

    /**
     * @param Schema $schema
     * @param string $table
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public static function addToSchema(Schema $schema, $table)
    {
        if ($schema->hasTable($table)) {
            $table = $schema->getTable($table);
        } else {
            $table = $schema->createTable($table);
        }

        if (!$table->hasColumn('snapshot_id')) {
            $id = $table->addColumn('snapshot_id', 'integer', ['unsigned' => true]);
            $id->setAutoincrement(true);
            $table->setPrimaryKey(['snapshot_id']);
        }

        if (!$table->hasColumn('aggregate_id')) {
            $table->addColumn('aggregate_id', 'string', ['length' => 50]);
        }

        if (!$table->hasColumn('version')) {
            $table->addColumn('version', 'integer');
        }

        if (!$table->hasColumn('type')) {
            $table->addColumn('type', 'string', ['length' => 100]);
        }

        if (!$table->hasColumn('payload')) {
            $table->addColumn('payload', 'text');
        }

        if (!$table->hasColumn('created_at')) {
            $table->addColumn('created_at', 'string', ['length' => 50]);
        }
    }

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function dropSchema(Connection $connection)
    {
        $sm = $connection->getSchemaManager();
        $fromSchema = $sm->createSchema();
        $toSchema = clone $fromSchema;

        $toSchema->dropTable(static::TABLE_NAME);
        $sqls = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());

        foreach ($sqls as $sql) {
            $connection->executeQuery($sql);
        }
    }
}
