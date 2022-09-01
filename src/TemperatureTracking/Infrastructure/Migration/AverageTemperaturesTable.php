<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TemperatureTracking\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class AverageTemperaturesTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a `projection_average_temperatures` table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
CREATE TABLE projection_average_temperatures (
    location VARCHAR(1024) NOT NULL PRIMARY KEY,
    accumulated_temperature REAL NOT NULL,
    recordings_count INT NOT NULL,
    average_temperature REAL NOT NULL
)');
    }
}
