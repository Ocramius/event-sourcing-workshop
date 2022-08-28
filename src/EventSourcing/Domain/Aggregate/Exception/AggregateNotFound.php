<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception;

use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use RuntimeException;

use function sprintf;

/**
 * This exception is generally thrown when a lookup of an {@see Aggregate} results in
 * an empty history (no domain events in the event store).
 */
final class AggregateNotFound extends RuntimeException
{
    /**
     * @psalm-param AggregateId<AggregateType> $id
     *
     * @psalm-template AggregateType of Aggregate
     */
    public static function forAggregateId(AggregateId $id): self
    {
        return new self(sprintf(
            'Could not locate aggregate "%s" with identifier "%s"',
            $id->aggregateType(),
            $id->toString(),
        ));
    }
}
