<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

interface EventStore
{
    /**
     * @psalm-assert !AggregateDomainEvent $event aggregate domain events cannot be saved directly: please use
     *               an {@see AggregateChanged} instead.
     */
    public function save(DomainEvent ...$domainEvent): void;

    /**
     * @param array{
     *     no?: non-empty-list<positive-int>,
     *     no_after?: positive-int|0,
     *     event_type?: non-empty-list<class-string<DomainEvent>>,
     *     time_of_recording_after?: DateTimeImmutable,
     *     time_of_recording_before?: DateTimeImmutable,
     * } $filter
     */
    public function stream(array $filter): iterable;
}