<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Unit\Domain\Aggregate;

use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged */
final class AggregateChangedTest extends TestCase
{
    public function testChanged(): void
    {
        /** @var AggregateId<Aggregate> $id */
        $id = $this->createMock(AggregateId::class);
        /** @var AggregateDomainEvent<Aggregate> $event1 */
        $event1 = $this->createMock(AggregateDomainEvent::class);
        /** @var AggregateDomainEvent<Aggregate> $event2 */
        $event2 = $this->createMock(AggregateDomainEvent::class);

        $aggregateChanged = AggregateChanged::changed($id, [$event1, $event2], 123);

        self::assertSame($id, $aggregateChanged->aggregate);
        self::assertSame([$event1, $event2], $aggregateChanged->raisedEvents);
        self::assertSame(123, $aggregateChanged->previousVersion);
    }

    public function testCreated(): void
    {
        /** @var AggregateId<Aggregate> $id */
        $id = $this->createMock(AggregateId::class);
        /** @var AggregateDomainEvent<Aggregate> $event1 */
        $event1 = $this->createMock(AggregateDomainEvent::class);
        /** @var AggregateDomainEvent<Aggregate> $event2 */
        $event2 = $this->createMock(AggregateDomainEvent::class);

        $aggregateChanged = AggregateChanged::created($id, [$event1, $event2]);

        self::assertSame($id, $aggregateChanged->aggregate);
        self::assertSame([$event1, $event2], $aggregateChanged->raisedEvents);
        self::assertSame(0, $aggregateChanged->previousVersion);
    }
}
