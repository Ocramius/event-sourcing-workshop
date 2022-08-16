<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/**
 * Definition of a projection that translates a given list of events into a database table.
 * 
 * @psalm-immutable
 */
interface DbTableProjectionDefinition
{
    /** @psalm-return non-empty-string */
    public function tableName(): string;

    /**
     * A series of callbacks that take an event and perform an operation on the given {@see ProjectionTable}, indexed
     * by the even that they apply to.
     *
     * @return callable[]
     * @psalm-return non-empty-array<
     *     class-string<DomainEvent>,
     *     callable(DomainEvent, ProjectionTable, Connection): void
     * >
     *
     * @psalm-pure
     */
    public function scheduledOperations(): array;
}
