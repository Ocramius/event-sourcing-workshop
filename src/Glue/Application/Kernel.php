#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Glue\Application;

use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\SQLite;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateRepository;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Aggregate\EventStreamAggregateRepository;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence\EventStore;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence\SQLiteEventStore;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEventWithValinorMapper;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStream;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStreamAndSaveStatusInSqlite;
use Lcobucci\Clock\SystemClock;
use StellaMaris\Clock\ClockInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * This class acts as a small container of instantiated services that we need to operate within our system.
 *
 * It is here to facilitate usage by workshop attendees, and to reduce the time needed to understand what
 * is available.
 */
final class Kernel
{
    public readonly Connection $db;
    public readonly ClockInterface $clock;
    public readonly DeSerializeEvent $deSerializeEvent;
    public readonly EventStore $eventStore;
    public readonly TraverseEventStream $traverseEventStream;

    public function __construct()
    {
        $this->db = DriverManager::getConnection([
            'driverClass' => SQLite\Driver::class,
            'path'        => __DIR__ . '/../../../data/database.sqlite',
        ]);

        $this->clock               = new SystemClock(new DateTimeZone('UTC'));
        $this->deSerializeEvent    = new DeSerializeEventWithValinorMapper(
            (new MapperBuilder())
                ->supportDateFormats(DateTimeImmutable::RFC3339_EXTENDED)
            ->mapper(),
        );
        $this->eventStore          = new SQLiteEventStore($this->db, $this->deSerializeEvent);
        $this->traverseEventStream = new TraverseEventStreamAndSaveStatusInSqlite($this->db, $this->deSerializeEvent);
    }

    /**
     * Need an {@see Aggregate}? Load it with this repository! Since our implementation is general-purpose,
     * we cheated a bit about the supported types, hence the suppressed psalm errors :)
     *
     * @param class-string<AggregateType> $aggregateType
     *
     * @return AggregateRepository<AggregateType>
     *
     * @template       AggregateType of Aggregate
     * @psalm-suppress UnusedParam we don't use $aggregateType: we only needed it for some type-level refinement
     * @psalm-suppress InvalidReturnStatement because the returned repository can handle all aggregate types, it is
     *                                        not specific for the given $aggregateType only.
     * @psalm-suppress InvalidReturnType because the returned repository can handle all aggregate types, it is
     *                                   not specific for the given $aggregateType only.
     */
    public function aggregateRepository(string $aggregateType): AggregateRepository
    {
        return new EventStreamAggregateRepository($this->db, $this->deSerializeEvent);
    }

    /**
     * Given a list of Doctrine Migrations class names, executes them against the internal database connection.
     *
     * If these migrations were already executed, this method will have no effect.
     *
     * Be aware that migrations are tracked by their name: if you change the contents of a DB migration, you
     * will probably need to delete the DB and restart from scratch.
     *
     * @param non-empty-list<class-string<AbstractMigration>> $migrations
     */
    public function ensureMigrationsRan(
        array $migrations = [
            CreateEventStreamTable::class,
            CreateEventStreamCursorsTable::class,
        ],
    ): void {
        $dependencyFactory = DependencyFactory::fromConnection(
            new ConfigurationArray(['migrations' => $migrations]),
            new ExistingConnection($this->db),
        );

        $dependencyFactory->getMetadataStorage()
            ->ensureInitialized();

        $input = new ArrayInput([]);

        $input->setInteractive(false);

        (new MigrateCommand($dependencyFactory))
            ->run($input, new NullOutput());
    }
}
