<?php

namespace HelloFresh\Engine\EventStore\Adapter\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DbalSchema
{
    const TABLE_NAME = 'events';

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
     * @param string $table
     */
    public static function addToSchema(Schema $schema, $table)
    {
        if ($schema->hasTable($table)) {
            $table = $schema->getTable($table);
        } else {
            $table = $schema->createTable($table);
        }

        if (!$table->hasColumn('event_id')) {
            $id = $table->addColumn('event_id', 'integer', ['unsigned' => true]);
            $id->setAutoincrement(true);
            $table->setPrimaryKey(['event_id']);
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

        if (!$table->hasColumn('recorded_on')) {
            $table->addColumn('recorded_on', 'string', ['length' => 50]);
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
