<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

/**
 * Represents a set of {@see AggregateDomainEvent} produced when interacting with an {@see Aggregate}:
 * this is designed to work well with {@see AggregateRepository}, so it is easy to save the changes that
 * occurred within an {@see Aggregate}.
 *
 * @psalm-immutable
 * @psalm-template AggregateType of Aggregate
 */
final class AggregateChanged
{
    /**
     * @psalm-param AggregateId<AggregateType>                $subject
     * @psalm-param list<AggregateDomainEvent<AggregateType>> $raisedEvents
     * @psalm-param 0|positive-int                            $previousVersion Reference to the version that the
     *                                                                         {@see Aggregate} had before these events
     *                                                                         were produced, so that it is possible to
     *                                                                         reconstruct the incremental version to be
     *                                                                         associated with each event.
     */
    private function __construct(
        public readonly AggregateId $aggregate,
        public readonly array $raisedEvents,
        public readonly int $previousVersion
    ) {
    }

    /**
     * @psalm-param AggregateId<TypedAggregate>                $aggregate
     * @psalm-param list<AggregateDomainEvent<TypedAggregate>> $raisedEvents
     * @psalm-param 0|positive-int                             $previousVersion
     *
     * @psalm-return self<TypedAggregate>
     *
     * @psalm-pure
     * @psalm-template TypedAggregate of Aggregate
     */
    public static function changed(
        AggregateId $aggregate,
        array $raisedEvents,
        int $previousVersion
    ): self {
        return new self($aggregate, $raisedEvents, $previousVersion);
    }

    /**
     * @psalm-param AggregateId<TypedAggregate>                $aggregate
     * @psalm-param list<AggregateDomainEvent<TypedAggregate>> $raisedEvents
     *
     * @psalm-return self<TypedAggregate>
     *
     * @psalm-pure
     * @psalm-template TypedAggregate of Aggregate
     */
    public static function created(
        AggregateId $aggregate,
        array $raisedEvents
    ): self {
        return new self($aggregate, $raisedEvents, 0);
    }
}
