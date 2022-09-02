<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Payment\Unit\Domain\Aggregate;

use DateTimeImmutable;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Aggregate\PaymentId;
use EventSourcingWorkshop\Payment\Domain\Amount;
use EventSourcingWorkshop\Payment\Domain\DebtorEmail;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentCompleted;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentDeadlineSet;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentRequested;
use PHPUnit\Framework\TestCase;

use function array_map;
use function assert;
use function get_class;

/** @covers \EventSourcingWorkshop\Payment\Domain\Aggregate\Payment */
final class PaymentTest extends TestCase
{
    public function testWillAllowRequestingAPayment(): void
    {
        $debtor  = new DebtorEmail('me@example.com');
        $amount  = new Amount(123);
        $time    = new DateTimeImmutable();
        $payment = Payment::requestPayment($debtor, $amount, $time);

        self::assertSame(0, $payment->previousVersion);
        self::assertInstanceOf(PaymentId::class, $payment->aggregate);

        $events = $payment->raisedEvents;

        self::assertCount(2, $events);

        self::assertInstanceOf(PaymentRequested::class, $events[0]);
        self::assertInstanceOf(PaymentDeadlineSet::class, $events[1]);
        self::assertEquals($time->modify('+5 day'), $events[1]->deadline);
    }

    public function testWillAllowMarkingAPaymentAsPaid(): void
    {
        $deadline = (new DateTimeImmutable())->modify('+50 day');
        $id       = PaymentId::generate();

        assert($deadline !== false);

        $payment = Payment::fromHistory(
            $id,
            [
                new PaymentRequested(
                    $id,
                    new DebtorEmail('me@example.com'),
                    new Amount(123),
                    new DateTimeImmutable(),
                ),
                new PaymentDeadlineSet(
                    $id,
                    $deadline,
                    new DateTimeImmutable(),
                ),
            ],
        );

        $paid = $payment->pay(new Amount(123), new DateTimeImmutable());

        self::assertEquals(2, $paid->previousVersion);
        self::assertEquals($id, $paid->aggregate);
        self::assertEquals(
            [PaymentCompleted::class],
            array_map(get_class(...), $paid->raisedEvents),
        );
    }
}
