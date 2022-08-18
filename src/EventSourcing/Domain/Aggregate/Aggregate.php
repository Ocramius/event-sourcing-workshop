<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain\Aggregate;

/**
 * An aggregate is an object representation of the state of a system, based on its past history.
 *
 * When interacting with an aggregate, we don't mutate its state, but rather emit events: this
 * is not represented by the interface itself, other than the fact that all aggregates are immutable.
 *
 * It is up to the implementor to decide whether invalid interactions with an aggregate should
 * produce no events (idempotency), produce "failure" events (auditing) or throw exceptions (crash,
 * acting as anti-corruption layer).
 *
 * @psalm-immutable
 */
interface Aggregate
{
    /**
     * We must be able to rebuild all internal state of an aggregate from its past history. That
     * history cannot be empty either, since "no history" also means "no aggregate".
     *
     * The signature containing an `AggregateType` template is just in place to enforce that the
     * given `$id` and `$history` must contain objects matching the same aggregate type.
     *
     * @param AggregateId<AggregateType>                          $id
     * @param non-empty-list<AggregateDomainEvent<AggregateType>> $history
     *
     * @template AggregateType of Aggregate
     * @psalm-pure
     */
    public static function fromHistory(AggregateId $id, array $history): static;
}
