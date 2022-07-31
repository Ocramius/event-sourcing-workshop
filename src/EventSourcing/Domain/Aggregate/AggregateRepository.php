<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception\AggregateNotFound;

/**
 * Allows loading an {@see Aggregate}, then interacting with it, and finally saving
 * the {@see AggregateChanged} resulting from the internal {@see Aggregate} state
 * mutations.
 *
 * @psalm-template AggregateType of Aggregate
 */
interface AggregateRepository
{
    /** @psalm-param AggregateChanged<AggregateType> $changed */
    public function save(AggregateChanged $changed): void;

    /**
     * @psalm-param AggregateId<AggregateType> $id
     *
     * @psalm-return AggregateType
     *
     * @throws AggregateNotFound
     */
    public function get(AggregateId $id): Aggregate;
}
