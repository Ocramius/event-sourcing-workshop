<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Integration\Infrastructure\Aggregate;

use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEventWithValinorMapper;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStreamAndSaveStatusInSqlite;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent1;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent2;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent3;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\GreetingId;
use EventSourcingWorkshopTest\EventSourcing\Integration\Support\EventSourcingTestHelper;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

use function get_class;
use function iterator_to_array;

/** @covers \EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStreamAndSaveStatusInSqlite */
final class TraverseEventStreamAndSaveStatusInSqliteTest extends TestCase
{
    private Connection $db;
    private DeSerializeEvent $loadEvent;
    private TraverseEventStreamAndSaveStatusInSqlite $traverse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db        = EventSourcingTestHelper::freshDatabase();
        $this->loadEvent = new DeSerializeEventWithValinorMapper(
            (new MapperBuilder())
                ->registerConstructor([GreetingId::class, 'fromString'])
                ->supportDateFormats(DateTimeImmutable::RFC3339_EXTENDED)
                ->mapper(),
        );

        EventSourcingTestHelper::runDatabaseMigrations(
            $this->db,
            [
                CreateEventStreamTable::class,
                CreateEventStreamCursorsTable::class,
            ],
        );
        $this->traverse = new TraverseEventStreamAndSaveStatusInSqlite($this->db, $this->loadEvent);
    }

    public function testTraversingEventStreamWillRegisterTraversal(): void
    {
        self::assertEmpty(iterator_to_array(($this->traverse)('example')));
        self::assertSame(
            [
                [
                    'name'               => 'example',
                    'last_seen_event_no' => 0,
                ],
            ],
            $this->db->fetchAllAssociative('SELECT name, last_seen_event_no FROM event_stream_cursors'),
            'Traversal has been registered',
        );
    }

    public function testFullTraversalOfNonEmptyEventStreamWillUpdateCursorPosition(): void
    {
        $event1 = DomainEvent1::dummy();
        $event2 = DomainEvent2::dummy();
        $event3 = DomainEvent3::dummy();

        EventSourcingTestHelper::appendEvents($this->db, [$event1, $event3, $event2]);

        self::assertSame(
            [DomainEvent1::class, DomainEvent3::class, DomainEvent2::class],
            Dict\map(($this->traverse)('example'), get_class(...)),
        );
        self::assertSame(
            [
                [
                    'name'               => 'example',
                    'last_seen_event_no' => 3,
                ],
            ],
            $this->db->fetchAllAssociative('SELECT name, last_seen_event_no FROM event_stream_cursors'),
        );
    }

    public function testFullTraversalWillNotBeRepeated(): void
    {
        $event1 = DomainEvent1::dummy();
        $event2 = DomainEvent2::dummy();
        $event3 = DomainEvent3::dummy();

        EventSourcingTestHelper::appendEvents($this->db, [$event1, $event3, $event2]);

        self::assertSame(3, Iter\count(($this->traverse)('example')));
        self::assertSame(
            0,
            Iter\count(($this->traverse)('example')),
            'Repeated named traversal has no effect',
        );

        self::assertSame(
            [
                [
                    'name'               => 'example',
                    'last_seen_event_no' => 3,
                ],
            ],
            $this->db->fetchAllAssociative('SELECT name, last_seen_event_no FROM event_stream_cursors'),
        );
    }

    public function testTraversalWillBeResumed(): void
    {
        $event1 = DomainEvent1::dummy();
        $event2 = DomainEvent1::dummy();
        $event3 = DomainEvent2::dummy();
        $event4 = DomainEvent3::dummy();

        EventSourcingTestHelper::appendEvents($this->db, [$event1, $event2, $event3]);

        self::assertSame(3, Iter\count(($this->traverse)('example')));

        EventSourcingTestHelper::appendEvents($this->db, [$event4]);

        self::assertSame(
            [DomainEvent3::class],
            Dict\map(($this->traverse)('example'), get_class(...)),
            'Traversal resumed from right after $event3',
        );

        self::assertSame(
            [
                [
                    'name'               => 'example',
                    'last_seen_event_no' => 4,
                ],
            ],
            $this->db->fetchAllAssociative('SELECT name, last_seen_event_no FROM event_stream_cursors'),
        );
    }

    public function testTraversalWithDifferentNameWillRepeatTraversal(): void
    {
        $event1 = DomainEvent1::dummy();
        $event2 = DomainEvent1::dummy();
        $event3 = DomainEvent2::dummy();

        EventSourcingTestHelper::appendEvents($this->db, [$event1, $event2, $event3]);

        self::assertSame(3, Iter\count(($this->traverse)('example1')));
        self::assertSame(3, Iter\count(($this->traverse)('example2')));
        self::assertSame(3, Iter\count(($this->traverse)('example3')));

        self::assertSame(
            [
                [
                    'name'               => 'example1',
                    'last_seen_event_no' => 3,
                ],
                [
                    'name'               => 'example2',
                    'last_seen_event_no' => 3,
                ],
                [
                    'name'               => 'example3',
                    'last_seen_event_no' => 3,
                ],
            ],
            $this->db->fetchAllAssociative('SELECT name, last_seen_event_no FROM event_stream_cursors ORDER BY name ASC'),
        );
    }
}
