<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain\DomainEvent;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Aggregate\PaymentId;

/**
 * @psalm-immutable
 * @template-implements AggregateDomainEvent<Payment>
 */
final class PaymentDeadlineSet implements AggregateDomainEvent
{
    public function __construct(
        public readonly PaymentId $payment,
        public readonly DateTimeImmutable $deadline,
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
            'deadline' => $this->deadline->format(DateTimeImmutable::RFC3339_EXTENDED),
            'raisedAt' => $this->raisedAt->format(DateTimeImmutable::RFC3339_EXTENDED),
        ];
    }
}
