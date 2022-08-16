<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/**
 * @psalm-immutable
 * @psalm-template AggregateType of Aggregate
 */
interface AggregateDomainEvent extends DomainEvent
{
    /** @psalm-return AggregateId<AggregateType> */
    public function aggregate(): AggregateId;
}
