<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Unit\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProcessProjectionOnTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProjectionTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStream;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent1;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent2;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent3;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProcessProjectionOnTable */
final class ProcessProjectionOnTableTest extends TestCase
{
    public function testWillApplyMappedOperations(): void
    {
        $tableDefinition = $this->createMock(DbTableProjectionDefinition::class);
        $table           = $this->createStub(ProjectionTable::class);
        $connection      = $this->createStub(Connection::class);
        $traverseStream  = $this->createMock(TraverseEventStream::class);
        $event1Callback  = $this->createMock(MockableFunction::class);
        $event2Callback  = $this->createMock(MockableFunction::class);

        $tableDefinition->method('tableName')
            ->willReturn('a_table_name');
        $tableDefinition->method('scheduledOperations')
            ->willReturn([
                DomainEvent1::class => $event1Callback,
                DomainEvent2::class => $event2Callback,
            ]);

        $event1 = DomainEvent1::dummy();
        $event2 = DomainEvent2::dummy();
        $event3 = DomainEvent3::dummy();

        $event1Callback->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($event1), $table, $connection);
        $event2Callback->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($event2), $table, $connection);

        $traverseStream->expects(self::once())
            ->method('__invoke')
            ->with('a_table_name')
            ->willReturn([$event3, $event1, $event2]);

        (new ProcessProjectionOnTable($tableDefinition, $table, $connection, $traverseStream))();
    }
}
