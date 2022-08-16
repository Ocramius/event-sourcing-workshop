<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Integration\Support;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandBus;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;
use EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProcessProjectionOnTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStreamAndSaveStatusInSqlite;
use PHPUnit\Framework\Assert as PHPUnitAssertions;
use Psl\Json;
use Psl\Type;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Webmozart\Assert\Assert;

use function array_map;
use function Psl\Json\encode;
use function Psl\Type\non_empty_string;
use function Psl\Type\shape;
use function Psl\Type\vec;

/**
 * This static utility will perform common operations within the context of an event-sourced business domain.
 */
final class EventSourcingTestHelper
{
    public static function freshDatabase(): Connection
    {
        return DriverManager::getConnection([
            'driverClass' => Driver::class,
            'memory'      => true,
        ]);
    }

    /** @param non-empty-list<class-string<AbstractMigration>> $migrations */
    public static function runDatabaseMigrations(Connection $connection, array $migrations): void
    {
        $dependencyFactory = DependencyFactory::fromConnection(
            new ConfigurationArray(['migrations' => $migrations]),
            new ExistingConnection($connection)
        );

        $dependencyFactory->getMetadataStorage()
            ->ensureInitialized();

        $input = new ArrayInput([]);

        $input->setInteractive(false);

        (new MigrateCommand($dependencyFactory))
            ->run($input, new NullOutput());
    }

    /** @psalm-param non-empty-list<DomainEvent> $events */
    public static function appendEvents(
        Connection $db,
        array $events
    ): void {
        /** @psalm-var array<non-empty-string, positive-int> */
        $aggregateVersions = [];

        foreach ($events as $event) {
            $aggregateId   = null;
            $aggregateType = null;
            $version       = null;

            if ($event instanceof AggregateDomainEvent) {
                $aggregate     = $event->aggregate();
                $aggregateType = $aggregate->aggregateType();
                $aggregateId   = $aggregate->toUuid()
                    ->getBytes();
                $version       = $aggregateVersions[$aggregateId] ?? 1;
            }

            $db->insert(
                'event_stream',
                [
                    'event_type'             => $event::class,
                    'aggregate_root_type'    => $aggregateType,
                    'aggregate_root_id'      => $aggregateId,
                    'aggregate_root_version' => $version,
                    'time_of_recording'      => $event->raisedAt()
                        ->format('Y-m-d H:i:s.u'),
                    'payload'                => encode($event->toArray()),
                ]
            );

            if (! ($event instanceof AggregateDomainEvent) || $version === null) {
                continue;
            }

            $aggregateVersions[$event->aggregate()->toUuid()->getBytes()] = $version + 1;
        }
    }

    /**
     * @psalm-param non-empty-list<class-string<AggregateDomainEvent<AggregateType>>> $expectedEvents
     * @psalm-param non-empty-string                                                  $message
     *
     * @psalm-template AggregateType of Aggregate
     */
    public static function assertRaisedEventTypesSequence(
        Connection $db,
        array $expectedEvents,
        string $message = 'Given sequence of event matches what is stored in the whole event store'
    ): void {
        PHPUnitAssertions::assertEquals(
            $expectedEvents,
            vec(non_empty_string())
                ->coerce($db->fetchFirstColumn(
                    <<<'SQL'
SELECT
    event_type
FROM
    event_stream
ORDER BY
    no ASC
SQL
                )),
            $message
        );
    }

    /**
     * @psalm-param list<AggregateDomainEvent<AggregateType>> $expected
     * @psalm-param list<AggregateDomainEvent<AggregateType>> $actual
     * @psalm-param non-empty-string                          $message
     *
     * @psalm-template AggregateType of Aggregate
     */
    public static function assertEquivalentEvents(
        array $expected,
        array $actual,
        string $message = 'Provided list of events are equivalent (ignoring the time at which they were raised)'
    ): void {
        $convert = static fn (AggregateDomainEvent $event): array => [
            'class'   => $event::class,
            'payload' => $event->toArray(),
        ];

        PHPUnitAssertions::assertEquals(
            array_map($convert, $expected),
            array_map($convert, $actual),
            $message
        );
    }

    /**
     * @see            Aggregate
     *
     * @psalm-param AggregateId<AggregateType> $id
     *
     * @psalm-return list<AggregateDomainEvent<AggregateType>>
     *
     * @psalm-template AggregateType of Aggregate
     */
    public static function fetchAllEventsForAggregate(
        Connection $db,
        DeSerializeEvent $loadEvent,
        AggregateId $id,
    ): array {
        return array_map(
            static function (array $row) use ($loadEvent): AggregateDomainEvent {
                Assert::implementsInterface($row['event_type'], AggregateDomainEvent::class);
                Assert::implementsInterface($row['aggregate_root_type'], Aggregate::class);

                /** @var AggregateDomainEvent<AggregateType> $event no real type inference possible here */
                $event = $loadEvent($row['event_type'], Json\typed($row['payload'], Type\dict(Type\non_empty_string(), Type\mixed())));

                return $event;
            },
            vec(shape([
                'event_type'          => non_empty_string(),
                'aggregate_root_type' => non_empty_string(),
                'time_of_recording'   => non_empty_string(),
                'payload'             => non_empty_string(),
            ]))
                ->coerce($db->fetchAllAssociative(
                    <<<'SQL'
SELECT
    event_type,
    aggregate_root_type,
    time_of_recording,
    payload
FROM
    event_stream
WHERE
    aggregate_root_id = :id
ORDER BY
    aggregate_root_version ASC
SQL
                    ,
                    [
                        'id' => $id->toUuid()
                            ->getBytes(),
                    ],
                    ['id' => Types::STRING]
                ))
        );
    }

    public static function fetchLastChangedAggregateId(Connection $db): UuidInterface
    {
        return Uuid::fromBytes(
            non_empty_string()
                ->coerce($db->fetchOne('SELECT aggregate_root_id FROM event_stream ORDER BY no DESC LIMIT 1'))
        );
    }

    public static function runProjector(
        DbTableProjectionDefinition $definition,
        Connection $connection,
        DeSerializeEvent $loadEvent
    ): void {
        ProcessProjectionOnTable::forDefinition(
            $definition,
            $connection,
            new TraverseEventStreamAndSaveStatusInSqlite(
                $connection,
                $loadEvent
            )
        )();
    }

    /**
     * @see      DomainEvent
     *
     * @param list<Policy<DomainEventType>> $policies
     *
     * @template DomainEventType of DomainEvent
     */
    public static function runPolicies(
        Connection $connection,
        CommandBus $commandBus,
        DeSerializeEvent $loadEvent,
        array $policies
    ): void {
        (new ProcessPolicies(
            $policies,
            $commandBus,
            new TraverseEventStreamAndSaveStatusInSqlite(
                $connection,
                $loadEvent,
            )
        ))();
    }
}
