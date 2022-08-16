<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Projection;

use Doctrine\DBAL\Connection;

use function array_fill;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function implode;

final class SQLiteProjectionTable implements ProjectionTable
{
    /** @psalm-var non-empty-string */
    private readonly string $tableName;

    public function __construct(private readonly Connection $connection, DbTableProjectionDefinition $tableDefinition)
    {
        $this->tableName  = $tableDefinition->tableName();
    }

    /** {@inheritDoc} */
    public function insertIgnore(array $record): void
    {
        $this->connection->executeStatement(
            'INSERT OR IGNORE INTO ' . $this->tableName . ' (' . implode(',', array_keys($record)) . ') '
            . 'VALUES (' . implode(',', array_fill(0, count($record), '?')) . ')',
            array_values($record)
        );
    }

    /** {@inheritDoc} */
    public function upsert(array $record): void
    {
        $columns = array_keys($record);

        $this->connection->executeStatement(
            'INSERT INTO ' . $this->tableName . ' (' . implode(',', $columns) . ') '
            . 'VALUES (' . implode(',', array_fill(0, count($record), '?')) . ') '
            . 'ON CONFLICT DO UPDATE SET ' . implode(',', array_map(
                static function (string $column): string {
                    return $column . '=excluded.' . $column;
                },
                $columns
            )),
            array_values($record)
        );
    }

    /** {@inheritDoc} */
    public function update(array $criteria, array $values): void
    {
        $this->connection->update($this->tableName, $values, $criteria);
    }

    /** {@inheritDoc} */
    public function delete(array $criteria): void
    {
        $this->connection->delete($this->tableName, $criteria);
    }

    public function truncate(): void
    {
        $this->connection->executeStatement('TRUNCATE TABLE ' . $this->tableName);
    }
}
