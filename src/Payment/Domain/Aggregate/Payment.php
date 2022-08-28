<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain\Aggregate;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshop\Payment\Domain\Amount;
use EventSourcingWorkshop\Payment\Domain\DebtorEmail;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentCompleted;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentDeadlineSet;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentRequested;

use function assert;

/**
 * This aggregate abstracts the lifecycle of a payment.
 *
 * It tracks what {@see Amount} is to be paid, and allows us to write business logic
 * for when a payment is completed, or when an {@see DebtorEmail} pays too much, and
 * determines the deadline for a payment.
 *
 * You can imagine this as an entity, except that instead of having fields, it has
 * a state that is something like (for example):
 *
 * * [{@see PaymentRequested}, {@see PaymentDeadlineSet}]
 * * [{@see PaymentRequested}, {@see PaymentDeadlineSet}, {@see PaymentCompleted}]
 *
 * @psalm-immutable
 */
final class Payment implements Aggregate
{
    private Amount|null $amount = null;
    /** @var positive-int|0 */
    private int $version = 0;

    private function __construct(private readonly PaymentId $id)
    {
    }

    /** @return AggregateChanged<self> */
    public static function requestPayment(
        DebtorEmail $debtor,
        Amount $amount,
        DateTimeImmutable $time,
    ): AggregateChanged {
        $id = PaymentId::generate();

        return AggregateChanged::created(
            PaymentId::generate(),
            [
                new PaymentRequested($id, $debtor, $amount, $time),
                // We hardcode the deadline, for now: good enough for us
                new PaymentDeadlineSet($id, $time->modify('+5 day'), $time),
            ],
        );
    }

    /** @return AggregateChanged<self> */
    public function pay(Amount $amount, DateTimeImmutable $time): AggregateChanged
    {
        if ($this->amount === null) {
            // @TODO what happens if this payment was alredady completed?
            return AggregateChanged::changed(
                $this->id,
                [],
                $this->version,
            );
        }

        if (! $this->amount->matches($amount)) {
            // @TODO what if the amount is smaller or greater than the requested payment?
            return AggregateChanged::changed(
                $this->id,
                [],
                $this->version,
            );
        }

        return AggregateChanged::changed(
            $this->id,
            [new PaymentCompleted($this->id, $time)],
            $this->version,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-pure
     */
    public static function fromHistory(AggregateId $id, array $history): static
    {
        assert($id instanceof PaymentId);
        $aggregate = new self($id);

        foreach ($history as $event) {
            $aggregate->version += 1;

            if ($event instanceof PaymentRequested) {
                $aggregate->amount = $event->amount;
            }

            if (! ($event instanceof PaymentCompleted)) {
                continue;
            }

            $aggregate->amount = null;
        }

        return $aggregate;
    }
}
