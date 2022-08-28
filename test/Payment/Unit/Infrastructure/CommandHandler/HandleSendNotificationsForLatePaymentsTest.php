<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Payment\Unit\Infrastructure\CommandHandler;

use DateTimeImmutable;
use EventSourcingWorkshop\Payment\Domain\Aggregate\PaymentId;
use EventSourcingWorkshop\Payment\Domain\Command\SendNotificationsForLatePayments;
use EventSourcingWorkshop\Payment\Infrastructure\CommandHandler\HandleSendNotificationsForLatePayments;
use EventSourcingWorkshopTest\EventSourcing\Integration\Support\EventSourcingTestHelper;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\Payment\Infrastructure\CommandHandler\HandleSendNotificationsForLatePayments */
final class HandleSendNotificationsForLatePaymentsTest extends TestCase
{
    public function testWillFindLatePaymentsToProcess(): void
    {
        $deadline        = new DateTimeImmutable();
        $notLateDate     = $deadline->modify('+1 day');
        $lateDate        = $deadline->modify('-1 day');
        $notLate         = PaymentId::generate();
        $late1           = PaymentId::generate();
        $late2           = PaymentId::generate();
        $lateButNoDebtor = PaymentId::generate();
        $db              = EventSourcingTestHelper::freshDatabase();

        $db->executeStatement(<<<'SQL'
CREATE TABLE projection_pending_payments (
    payment VARCHAR(1024) PRIMARY KEY NOT NULL,
    debtor VARCHAR(1024) DEFAULT NULL,
    deadline DATETIME DEFAULT NULL
)
SQL);
        $db->insert(
            'projection_pending_payments',
            [
                'payment'  => $notLate->toString(),
                'debtor'   => 'not-late@example.com',
                'deadline' => $notLateDate->format(DateTimeImmutable::RFC3339_EXTENDED),
            ],
        );
        $db->insert(
            'projection_pending_payments',
            [
                'payment'  => $late1->toString(),
                'debtor'   => 'late-1@example.com',
                'deadline' => $lateDate->format(DateTimeImmutable::RFC3339_EXTENDED),
            ],
        );
        $db->insert(
            'projection_pending_payments',
            [
                'payment'  => $late2->toString(),
                'debtor'   => 'late-2@example.com',
                'deadline' => $lateDate->format(DateTimeImmutable::RFC3339_EXTENDED),
            ],
        );
        $db->insert(
            'projection_pending_payments',
            [
                'payment'  => $lateButNoDebtor->toString(),
                'deadline' => $lateDate->format(DateTimeImmutable::RFC3339_EXTENDED),
            ],
        );

        $log = $this->createMock(MockableCallback::class);

        $log->expects(self::exactly(2))
            ->method('__invoke')
            ->with(self::logicalOr(
                'Notifying late-1@example.com of late payment for ' . $late1->toString(),
                'Notifying late-2@example.com of late payment for ' . $late2->toString(),
            ));

        /** @psalm-var callable(string): void $log */
        (new HandleSendNotificationsForLatePayments($db, $log))(new SendNotificationsForLatePayments($deadline));
    }
}
