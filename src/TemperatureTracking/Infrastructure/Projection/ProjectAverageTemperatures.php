<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TemperatureTracking\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProjectionTable;
use EventSourcingWorkshop\TemperatureTracking\Domain\TemperatureRecorded;

/** @psalm-immutable */
final class ProjectAverageTemperatures implements DbTableProjectionDefinition
{
    public function tableName(): string
    {
        return 'projection_average_temperatures';
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificReturnType by design, these closures have a more specific event parameter type than
     *                 just {@see DomainEvent}
     * @psalm-suppress LessSpecificReturnStatement by design, these closures have a more specific event parameter type
     *                 than just {@see DomainEvent}
     */
    public function scheduledOperations(): array
    {
        return [
            TemperatureRecorded::class => static function (
                TemperatureRecorded $event,
                ProjectionTable $table,
                Connection $connection,
            ): void {
                $table->insertIgnore([
                    'location'                => $event->location,
                    'accumulated_temperature' => 0,
                    'recordings_count'        => 0,
                    'average_temperature'     => 0,
                ]);

                $connection->executeStatement(
                    <<<'SQL'
UPDATE projection_average_temperatures
SET
    accumulated_temperature = accumulated_temperature + :temperature,
    recordings_count = recordings_count + 1,
    average_temperature = (accumulated_temperature + :temperature) / (recordings_count + 1)
WHERE location = :location
SQL,
                    [
                        'location' => $event->location,
                        'temperature' => $event->celsius,
                    ],
                );
            },
        ];
    }
}
