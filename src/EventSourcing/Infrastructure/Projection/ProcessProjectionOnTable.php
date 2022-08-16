<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStream;
use function get_class;

final class ProcessProjectionOnTable
{
    public function __construct(
        private readonly DbTableProjectionDefinition $definition,
        private readonly ProjectionTable             $table,
        private readonly Connection                  $connection,
        private readonly TraverseEventStream         $traverseEventStream
    ) {
    }

    public static function forDefinition(
        DbTableProjectionDefinition $definition,
        Connection                  $connection,
        TraverseEventStream         $traverseEventStream
    ): self {
        return new self(
            $definition,
            new SQLiteProjectionTable($connection, $definition),
            $connection,
            $traverseEventStream
        );
    }

    public function __invoke(): void
    {
        $tableName  = $this->definition->tableName();
        $operations = $this->definition->scheduledOperations();

        foreach (($this->traverseEventStream)($tableName) as $event) {
            $eventType = get_class($event);

            if (! array_key_exists($eventType, $operations)) {
                continue;
            }

            $operations[$eventType]($event, $this->table, $this->connection);
        }
    }
}
