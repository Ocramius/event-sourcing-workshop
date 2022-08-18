<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Unit\Domain\Aggregate\Exception;

use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception\AggregateNotFound;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception\AggregateNotFound */
final class AggregateNotFoundTest extends TestCase
{
    public function testForAggregateId(): void
    {
        $id = $this->createStub(AggregateId::class);

        $id->method('toString')
            ->willReturn('foo bar baz');
        $id->method('aggregateType')
            ->willReturn('An\Aggregate');

        self::assertSame(
            'Could not locate aggregate "An\Aggregate" with identifier "foo bar baz"',
            AggregateNotFound::forAggregateId($id)
                ->getMessage()
        );
    }
}
