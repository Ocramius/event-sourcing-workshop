<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain\DomainEvent;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Aggregate\PaymentId;
use EventSourcingWorkshop\Payment\Domain\Amount;
use EventSourcingWorkshop\Payment\Domain\DebtorEmail;

/**
 * @psalm-immutable
 * @template-implements AggregateDomainEvent<Payment>
 */
final class PaymentRequested implements AggregateDomainEvent
{
    public function __construct(
        public readonly PaymentId $payment,
        public readonly DebtorEmail $debtor,
        public readonly Amount $amount,
        public readonly DateTimeImmutable $raisedAt,
    ) {
    }

    public function aggregate(): AggregateId
    {
        return $this->payment;
    }

    public function raisedAt(): DateTimeImmutable
    {
        return $this->raisedAt;
    }

    /** {@inheritDoc} */
    public function toArray(): array
    {
        return [
            'payment'  => $this->payment->toString(),
            'amount'   => $this->amount->amount,
            'debtor'   => $this->debtor->email,
            'raisedAt' => $this->raisedAt->format(DateTimeImmutable::RFC3339_EXTENDED),
        ];
    }
}
