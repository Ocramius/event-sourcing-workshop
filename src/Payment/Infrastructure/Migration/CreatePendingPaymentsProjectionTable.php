<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class CreatePendingPaymentsProjectionTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a `projection_pending_payments` table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
CREATE TABLE projection_pending_payments (
    payment VARCHAR(1024) NOT NULL PRIMARY KEY,
    debtor VARCHAR(1024) DEFAULT NULL,
    deadline DATETIME DEFAULT NULL
)');
    }
}
