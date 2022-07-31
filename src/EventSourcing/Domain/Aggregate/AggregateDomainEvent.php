<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/**
 * @psalm-immutable
 * @psalm-template AggregateType of Aggregate
 */
interface AggregateDomainEvent extends DomainEvent
{
    /** @psalm-return AggregateId<AggregateType> */
    public function aggregate(): AggregateId;

    /**
     * @param array<non-empty-string, string|int|bool|float|array|null> $data
     * @psalm-param AggregateId<AggregateTypeForId>                     $aggregate
     *
     * @psalm-template AggregateTypeForId of Aggregate
     * @psalm-pure
     */
    public static function tryFrom(
        AggregateId $aggregate,
        DateTimeImmutable $raisedAt,
        array $data // @TODO use valinor here?
    ): static; // @TODO JSONSerialize?
}
