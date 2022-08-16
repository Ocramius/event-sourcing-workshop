<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example;

use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver;
use Doctrine\DBAL\DriverManager;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandBus;
use EventSourcingWorkshop\Commanding\Infrastructure\HandleCommandThroughGivenCommandHandlers;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateRepository;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception\AggregateNotFound;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Aggregate\EventStreamAggregateRepository;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEventWithValinorMapper;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command\SayGoodbye;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command\SayHello;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\GoodbyeSaid;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\HelloSaid;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Greeting;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\GreetingId;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Policy\WhenHelloSaidThenSayGoodbye;
use EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\CommandHandler\SayGoodbyeHandler;
use EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\CommandHandler\SayHelloHandler;
use EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\Migration\CreateTestProjectionTables;
use EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\Projection\DateOfLastDispensedGreeting;
use EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\Projection\PendingGoodbyes;
use EventSourcingWorkshopTest\EventSourcing\Integration\Support\EventSourcingTestHelper;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;
use StellaMaris\Clock\ClockInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use function Psl\Type\string;

/** @coversNothing */
final class GreetingExampleDomainIntegrationTest extends TestCase
{
    private Connection $db;
    private CommandBus $commandBus;
    private DeSerializeEvent $loadEvent;
    private ClockInterface $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db        = EventSourcingTestHelper::freshDatabase();
        $this->loadEvent = new DeSerializeEventWithValinorMapper(
            (new MapperBuilder())
                ->registerConstructor([GreetingId::class, 'fromString'])
                ->mapper()
        );

        EventSourcingTestHelper::runDatabaseMigrations(
            $this->db,
            [
                CreateEventStreamTable::class,
                CreateEventStreamCursorsTable::class,
                CreateTestProjectionTables::class,
            ]
        );

        /** @var AggregateRepository<Greeting> $aggregateRepository */
        $aggregateRepository = new EventStreamAggregateRepository($this->db, $this->loadEvent);

        $this->clock = new FrozenClock(new DateTimeImmutable());

        /**
         * @psalm-suppress InvalidArgument this command bus receives non-covariant command handlers: psalm is correctly
         *                                 identifying that, and attempting to prevent us from getting hurt, but we are
         *                                 explicitly going down that road here.
         */
        $this->commandBus = new HandleCommandThroughGivenCommandHandlers([
            new SayHelloHandler($aggregateRepository, $this->clock),
            new SayGoodbyeHandler($aggregateRepository, $this->clock),
        ]);
    }

    /** Create a new event-sourced aggregate root, resulting in new event_stream entries */
    public function testWillBeAbleToRecordNewGreetings(): void
    {
        $this->dispatchCommand(new SayHello('hi!'));

        EventSourcingTestHelper::assertRaisedEventTypesSequence(
            $this->db,
            [HelloSaid::class],
            'Saved an aggregate in form of a single ' . HelloSaid::class
        );
    }

    /** Manipulate a pre-existing event-sourced aggregate root, resulting in new `event_stream` entries */
    public function testAfterAGreetingWeCanSayGoodbye(): void
    {
        $this->dispatchCommand(new SayHello('hi!'));

        $greetingId = GreetingId::fromString(
            EventSourcingTestHelper::fetchLastChangedAggregateId($this->db)
                ->toString()
        );

        $this->dispatchCommand(new SayGoodbye($greetingId, 'goodbye!'));

        EventSourcingTestHelper::assertEquivalentEvents(
            [
                HelloSaid::raise($greetingId, 'hi!', $this->clock->now()),
                GoodbyeSaid::raise($greetingId, 'goodbye!', $this->clock->now()),
            ],
            EventSourcingTestHelper::fetchAllEventsForAggregate($this->db, $this->loadEvent, $greetingId),
            'A ' . GoodbyeSaid::class . ' associated to our previous ' . HelloSaid::class . ' was raised'
        );
    }

    /** The event-sourced aggregate prevents invalid state transitions */
    public function testCannotSayGoodbyeWhenAlreadyHavingSaidHello(): void
    {
        $this->dispatchCommand(new SayHello('hi!'));

        $greetingUuid = EventSourcingTestHelper::fetchLastChangedAggregateId($this->db);

        $this->dispatchCommand(new SayGoodbye(GreetingId::fromString($greetingUuid->toString()), 'goodbye 1'));

        $this->expectExceptionMessage('Goodbye is goodbye - we already said it!');
        $this->dispatchCommand(new SayGoodbye(GreetingId::fromString($greetingUuid->toString()), 'goodbye 2'));
    }

    /** Cannot manipulate a non-existing aggregate root */
    public function testCannotSayGoodbyeWithoutHavingSaidHello(): void
    {
        $this->expectException(AggregateNotFound::class);

        $this->dispatchCommand(new SayGoodbye(GreetingId::generate(), 'hi!'));
    }

    /** Policies can be triggered via background process, and they will react to raised events */
    public function testHelloWillBeFollowedByAnAutomatedGoodbye(): void
    {
        $this->dispatchCommand(new SayHello('joe'));
        $greetingId = GreetingId::fromString(
            EventSourcingTestHelper::fetchLastChangedAggregateId($this->db)
                ->toString()
        );

        EventSourcingTestHelper::runPolicies($this->db, $this->commandBus, $this->loadEvent, [new WhenHelloSaidThenSayGoodbye()]);

        EventSourcingTestHelper::assertEquivalentEvents(
            [
                HelloSaid::raise($greetingId, 'joe', $this->clock->now()),
                GoodbyeSaid::raise($greetingId, 'goodbye - joe', $this->clock->now()),
            ],
            EventSourcingTestHelper::fetchAllEventsForAggregate($this->db, $this->loadEvent, $greetingId),
            GoodbyeSaid::class . ' was raised automatically by policy + automation'
        );
    }

    /** Projectors can update the state of a DB table that depends on the event store history */
    public function testLastDispensedGreetingIsTracked(): void
    {
        $this->dispatchCommand(new SayHello('greeting 1'));
        $greeting1Date = DateTimeImmutable::createFromFormat(
            DateTimeImmutable::RFC3339_EXTENDED,
            string()->coerce(
                $this->db->fetchOne('SELECT payload ->> \'$.raisedAt\' FROM event_stream ORDER BY no DESC LIMIT 1')
            )
        );

        self::assertNotFalse($greeting1Date);

        $this->dispatchCommand(new SayHello('greeting 2'));
        $greeting2Date = DateTimeImmutable::createFromFormat(
            DateTimeImmutable::RFC3339_EXTENDED,
            string()->coerce(
                $this->db->fetchOne('SELECT payload ->> \'$.raisedAt\' FROM event_stream ORDER BY no DESC LIMIT 1')
            )
        );

        self::assertNotFalse($greeting2Date);

        self::assertEmpty(
            $this->db->fetchAllAssociative('SELECT * FROM projection_date_of_last_dispensed_greeting'),
            'Projection table is empty at first'
        );

        EventSourcingTestHelper::runProjector(new DateOfLastDispensedGreeting(), $this->db, $this->loadEvent);

        self::assertEquals(
            $greeting1Date,
            DateTimeImmutable::createFromFormat(
                DateTimeImmutable::RFC3339_EXTENDED,
                string()->assert($this->db->fetchOne('SELECT last_dispensed_greeting FROM projection_date_of_last_dispensed_greeting'))
            ),
            'Projection was updated with the last raised event'
        );

        EventSourcingTestHelper::runProjector(new DateOfLastDispensedGreeting(), $this->db, $this->loadEvent);

        self::assertEquals(
            $greeting2Date,
            DateTimeImmutable::createFromFormat(
                DateTimeImmutable::RFC3339_EXTENDED,
                string()->assert($this->db->fetchOne('SELECT last_dispensed_greeting FROM projection_date_of_last_dispensed_greeting'))
            ),
            'Projection was updated with the last raised event'
        );
    }

    /** Projectors update DB table that depends on the event store history, and handle multiple event types */
    public function testPendingGoodbyesAreTrackedAndDeletedOnceHandled(): void
    {
        $this->dispatchCommand(new SayHello('greeting 1'));
        $greeting1Uuid = EventSourcingTestHelper::fetchLastChangedAggregateId($this->db);

        $this->dispatchCommand(new SayHello('greeting 2'));
        $greeting2Uuid = EventSourcingTestHelper::fetchLastChangedAggregateId($this->db);

        self::assertEmpty(
            $this->db->fetchAllAssociative('SELECT * FROM projection_pending_goodbyes'),
            'Projection table is empty at first'
        );

        EventSourcingTestHelper::runProjector(new PendingGoodbyes(), $this->db, $this->loadEvent);
        self::assertCount(
            2,
            $this->db->fetchFirstColumn('SELECT * FROM projection_pending_goodbyes')
        );

        $this->dispatchCommand(new SayGoodbye(GreetingId::fromString($greeting2Uuid->toString()), 'goodbye 2'));
        $this->dispatchCommand(new SayGoodbye(GreetingId::fromString($greeting1Uuid->toString()), 'goodbye 1'));

        EventSourcingTestHelper::runProjector(new PendingGoodbyes(), $this->db, $this->loadEvent);
        self::assertEmpty(
            $this->db->fetchAllAssociative('SELECT * FROM projection_pending_goodbyes'),
            'Projection table is empty at first'
        );
    }

    private function dispatchCommand(SayHello|SayGoodbye $command): void
    {
        ($this->commandBus)($command);
    }
}
