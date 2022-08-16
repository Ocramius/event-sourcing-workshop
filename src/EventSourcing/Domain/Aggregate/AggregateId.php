<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-template AggregateType of Aggregate
 * @psalm-immutable
 */
interface AggregateId
{
    /** @psalm-return non-empty-string */
    public function toString(): string;

    /** Note: if the aggregate ID is not a UUID, you may consider generating a UUID5 */
    public function toUuid(): UuidInterface;

    /** @psalm-return class-string<AggregateType> */
    public function aggregateType(): string;
}
