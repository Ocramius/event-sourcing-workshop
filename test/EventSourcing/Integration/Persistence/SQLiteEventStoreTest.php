<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Integration\Persistence;

use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence\SQLiteEventStore;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEventWithValinorMapper;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent1;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent2;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent3;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\HelloSaid;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\GreetingId;
use EventSourcingWorkshopTest\EventSourcing\Integration\Support\EventSourcingTestHelper;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;

use function assert;
use function iterator_to_array;

/** @covers \EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence\SQLiteEventStore */
final class SQLiteEventStoreTest extends TestCase
{
    private Connection $db;
    private DeSerializeEventWithValinorMapper $deserializeEvents;
    private SQLiteEventStore $eventStore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db                = EventSourcingTestHelper::freshDatabase();
        $this->deserializeEvents = new DeSerializeEventWithValinorMapper(
            (new MapperBuilder())
                ->mapper()
        );

        EventSourcingTestHelper::runDatabaseMigrations($this->db, [CreateEventStreamTable::class]);

        $this->eventStore = new SQLiteEventStore($this->db, $this->deserializeEvents);
    }

    public function testWillStoreAndLoadEventsInTheRightOrder(): void
    {
        $event1 = new DomainEvent1($this->makeTime(), ['foo' => 'bar']);
        $event2 = new DomainEvent2($this->makeTime(), ['baz' => 'tab']);
        $event3 = new DomainEvent3($this->makeTime(), ['taz' => 'tar']);

        $this->eventStore->save($event2, $event1, $event3);

        $loaded = iterator_to_array($this->eventStore->stream([]));

        self::assertEquals([$event2, $event1, $event3], $loaded);
    }

    public function testWillFilterEventsBasedOnGivenTimeLimits(): void
    {
        $lastWeek  = $this->makeTime(new DateTimeImmutable('-7 days'));
        $yesterday = $this->makeTime(new DateTimeImmutable('-1 days'));
        $today     = $this->makeTime(new DateTimeImmutable());
        $tomorrow  = $this->makeTime(new DateTimeImmutable());

        $event1 = new DomainEvent1($lastWeek, ['foo' => 'bar']);
        $event2 = new DomainEvent2($yesterday, ['baz' => 'tab']);
        $event3 = new DomainEvent3($today, ['taz' => 'tar']);
        $event4 = new DomainEvent3($tomorrow, ['war' => 'waz']);

        $this->eventStore->save($event2, $event1, $event4, $event3);

        self::assertEquals(
            [$event4, $event3],
            iterator_to_array($this->eventStore->stream(['time_of_recording_after' => $today]))
        );

        self::assertEquals(
            [$event2, $event1],
            iterator_to_array($this->eventStore->stream(['time_of_recording_before' => $today]))
        );
    }

    public function testWillFilterEventsBasedOnTheirtype(): void
    {
        $event1 = new DomainEvent1($this->makeTime(), ['foo' => 'bar']);
        $event2 = new DomainEvent2($this->makeTime(), ['baz' => 'tab']);
        $event3 = new DomainEvent3($this->makeTime(), ['taz' => 'tar']);
        $event4 = new DomainEvent3($this->makeTime(), ['war' => 'waz']);

        $this->eventStore->save($event2, $event1, $event4, $event3);

        self::assertEquals(
            [$event1, $event4, $event3],
            iterator_to_array($this->eventStore->stream([
                'event_type' => [DomainEvent1::class, DomainEvent3::class],
            ]))
        );
    }

    public function testWillFetchEventsAfterACertainOffset(): void
    {
        $event1 = new DomainEvent1($this->makeTime(), ['foo' => 'bar']);
        $event2 = new DomainEvent2($this->makeTime(), ['baz' => 'tab']);
        $event3 = new DomainEvent3($this->makeTime(), ['taz' => 'tar']);
        $event4 = new DomainEvent3($this->makeTime(), ['war' => 'waz']);

        $this->eventStore->save($event2, $event1, $event4, $event3);

        self::assertEquals(
            [$event4, $event3],
            iterator_to_array($this->eventStore->stream(['no_after' => 2]))
        );
    }

    public function testWillFetchEventsWithACertainSequentialNumber(): void
    {
        $event1 = new DomainEvent1($this->makeTime(), ['foo' => 'bar']);
        $event2 = new DomainEvent2($this->makeTime(), ['baz' => 'tab']);
        $event3 = new DomainEvent3($this->makeTime(), ['taz' => 'tar']);
        $event4 = new DomainEvent3($this->makeTime(), ['war' => 'waz']);

        $this->eventStore->save($event2, $event1, $event4, $event3);

        self::assertEquals(
            [$event2, $event3],
            iterator_to_array($this->eventStore->stream([
                'no' => [1, 4],
            ]))
        );
    }

    public function testRefusesToSaveAggregateDomainEvents(): void
    {
        $event = HelloSaid::raise(GreetingId::generate(), 'hello', $this->makeTime());

        $this->expectException(InvariantViolationException::class);
        $this->eventStore->save($event);
    }

    public function testACrashDuringPersistenceLeadsToNoEventsBeingSaved(): void
    {
        $event1 = new DomainEvent1($this->makeTime(), ['foo' => 'bar']);
        $event2 = new DomainEvent2($this->makeTime(), ['baz' => 'tab']);
        $event3 = new DomainEvent3($this->makeTime(), ['taz' => 'tar']);
        $broken = HelloSaid::raise(GreetingId::generate(), 'hello', $this->makeTime());
        $event4 = new DomainEvent3($this->makeTime(), ['war' => 'waz']);

        try {
            $this->eventStore->save($event1, $event2, $event3, $broken, $event4);

            self::fail('Persistence should not complete successfully');
        } catch (InvariantViolationException) {
            self::assertEmpty(iterator_to_array($this->eventStore->stream([])));
        }
    }

    /**
     * Creating a time instance with {@see DateTimeImmutable::RFC3339_EXTENDED} time, and dropping anything
     * below milliseconds (which we don't currently deal with).
     */
    private function makeTime(?DateTimeImmutable $givenTime = null): DateTimeImmutable
    {
        $time = DateTimeImmutable::createFromFormat(
            DateTimeImmutable::RFC3339_EXTENDED,
            ($givenTime ?? new DateTimeImmutable())->format(DateTimeImmutable::RFC3339_EXTENDED)
        );

        assert($time !== false);

        return $time;
    }
}
