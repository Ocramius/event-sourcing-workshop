<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

/**
 * @psalm-template AggregateType of Aggregate
 * @psalm-immutable
 */
interface AggregateId
{
    /** @psalm-return non-empty-string */
    public function toString(): string;

    /** @psalm-return class-string<AggregateType> */
    public function aggregateType(): string;
}
