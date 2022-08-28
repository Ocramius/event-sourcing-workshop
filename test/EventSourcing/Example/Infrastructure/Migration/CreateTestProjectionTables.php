<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @psalm-suppress UnusedClass this class is only wired in the application configuration, and only for when
 *                 tests are supposed to be run.
 */
final class CreateTestProjectionTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return <<<'DESCRIPTION'
Adds test tables needed by our test suite to verify that projection table handling works as expected.

This migration should only be in use when APP_ENV=test
DESCRIPTION;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE projection_date_of_last_dispensed_greeting (
    -- this is just how you do ENUMs in SQLite
    position TEXT CHECK(position IN ('last')) NOT NULL PRIMARY KEY DEFAULT 'last',
    last_dispensed_greeting DATETIME(6) NOT NULL
);
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE projection_pending_goodbyes (
    greeting CHAR(36) NOT NULL PRIMARY KEY
);
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE projection_date_of_last_dispensed_greeting');
        $this->addSql('DROP TABLE projection_pending_goodbyes');
    }
}
