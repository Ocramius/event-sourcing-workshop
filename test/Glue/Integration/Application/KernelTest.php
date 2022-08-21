<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Glue\Integration\Application;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Aggregate\EventStreamAggregateRepository;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence\SQLiteEventStore;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStreamAndSaveStatusInSqlite;
use EventSourcingWorkshop\Glue\Application\Kernel;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\Glue\Application\Kernel */
final class KernelTest extends TestCase
{
    public function testKernelProvidesAllUtilities(): void
    {
        $kernel = new Kernel();

        self::assertEquals(1, $kernel->db->fetchOne('SELECT 1'));
        self::assertGreaterThanOrEqual(new DateTimeImmutable(), $kernel->clock->now());
        self::assertInstanceOf(SQLiteEventStore::class, $kernel->eventStore);
        self::assertInstanceOf(TraverseEventStreamAndSaveStatusInSqlite::class, $kernel->traverseEventStream);
        self::assertInstanceOf(EventStreamAggregateRepository::class, $kernel->aggregateRepository(Aggregate::class));

        $kernel->ensureMigrationsRan();
    }
}
