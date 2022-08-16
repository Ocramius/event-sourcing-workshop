<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Integration\Infrastructure\Aggregate;

use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception\AggregateNotFound;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Aggregate\EventStreamAggregateRepository;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEventWithValinorMapper;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\HelloSaid;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Greeting;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\GreetingId;
use EventSourcingWorkshopTest\EventSourcing\Integration\Support\EventSourcingTestHelper;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;
use StellaMaris\Clock\ClockInterface;

/** @covers \EventSourcingWorkshop\EventSourcing\Infrastructure\Aggregate\EventStreamAggregateRepository */
final class EventStreamAggregateRepositoryTest extends TestCase
{
    private Connection $db;
    private DeSerializeEvent $loadEvent;
    private ClockInterface $clock;
    private EventStreamAggregateRepository $aggregates;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db        = EventSourcingTestHelper::freshDatabase();
        $this->loadEvent = new DeSerializeEventWithValinorMapper(
            (new MapperBuilder())
                ->registerConstructor([GreetingId::class, 'fromString'])
                ->mapper()
        );
        $this->clock     = new FrozenClock(new DateTimeImmutable());

        EventSourcingTestHelper::runDatabaseMigrations($this->db, [CreateEventStreamTable::class]);
        $this->aggregates = new EventStreamAggregateRepository($this->db, $this->loadEvent);
    }

    public function testWillSaveAggregateChangedDiffs(): void
    {
        $greetingId = GreetingId::generate();
        $event1     = HelloSaid::raise($greetingId, 'hi', $this->clock->now());
        $event2     = HelloSaid::raise($greetingId, 'hi', $this->clock->now());

        /** @psalm-suppress InvalidArgument we are saving a more specific aggregate type than the one declared */
        $this->aggregates->save(AggregateChanged::changed($greetingId, [$event1, $event2], 4));

        self::assertEquals(
            [5, 6],
            $this->db->fetchFirstColumn('SELECT aggregate_root_version FROM event_stream ORDER BY no ASC'),
            'Aggregate versioning is correctly handled, and considers current version'
        );

        EventSourcingTestHelper::assertEquivalentEvents(
            [$event1, $event2],
            EventSourcingTestHelper::fetchAllEventsForAggregate($this->db, $this->loadEvent, $greetingId)
        );
    }

    public function testWillRetrieveAggregateByGivenId(): void
    {
        $greetingId = GreetingId::generate();
        $event1     = HelloSaid::raise($greetingId, 'hi', $this->clock->now());
        $event2     = HelloSaid::raise($greetingId, 'hi', $this->clock->now());

        EventSourcingTestHelper::appendEvents($this->db, [$event1, $event2]);

        /** @psalm-suppress InvalidArgument we are loading a more specific aggregate type than the one declared */
        self::assertEquals(
            Greeting::fromHistory($greetingId, [$event1, $event2]),
            $this->aggregates->get($greetingId)
        );
    }

    public function testWillRefuseToFindAggregateWithNoRecordedHistory(): void
    {
        $this->expectException(AggregateNotFound::class);

        /** @psalm-suppress InvalidArgument we are loading a more specific aggregate type than the one declared */
        $this->aggregates->get(GreetingId::generate());
    }
}
