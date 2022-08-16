<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class CreateEventStreamCursorsTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds `event_stream_cursors` table, representing a list of all cursors currently iterating over the event stream';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE event_stream_cursors (
    name VARCHAR(1024) PRIMARY KEY NOT NULL,
    last_seen_event_no INTEGER NOT NULL DEFAULT 0,
    CHECK (length(name) > 0)
)
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_stream_cursors');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
