<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class CreateEventStreamTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds `event_stream` table, representing a list of all events that ever occurred in this application';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE event_stream (
    no INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type VARCHAR(1024) NOT NULL,
    aggregate_root_type VARCHAR(1024) DEFAULT NULL,
    aggregate_root_id VARCHAR(1024) DEFAULT NULL,
    aggregate_root_version INT DEFAULT NULL,
    time_of_recording DATETIME NOT NULL,
    payload JSON NOT NULL,
    CONSTRAINT aggregate_root_must_be_set_together CHECK (
        (
            aggregate_root_type IS NOT NULL
            AND aggregate_root_id IS NOT NULL
            AND aggregate_root_version IS NOT NULL
        ) OR (
            aggregate_root_type IS NULL
            AND aggregate_root_id IS NULL
            AND aggregate_root_version IS NULL
        )
    )
)
SQL);
        $this->addSql('CREATE INDEX event_stream_aggregate_root_id ON event_stream (aggregate_root_id)');
        $this->addSql('CREATE INDEX event_stream_event_type_no ON event_stream (event_type, no)');

        // This index is an optimistic lock: two separate processes operating on the same aggregate will
        // not be able to change said aggregate at the same time, since the two processes will lead to
        // a duplicate key collision.
        $this->addSql('CREATE UNIQUE INDEX event_stream_unique_id_and_version ON event_stream (aggregate_root_id, aggregate_root_version)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_stream');
    }
}
