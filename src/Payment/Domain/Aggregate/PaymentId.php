<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain\Aggregate;

use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use Ramsey\Uuid\Uuid;

/**
 * @psalm-immutable
 * @template-implements AggregateId<Payment>
 */
final class PaymentId implements AggregateId
{
    /** @param non-empty-string $payment */
    public function __construct(private string $payment)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid6()->toString());
    }

    public function toString(): string
    {
        return $this->payment;
    }

    public function aggregateType(): string
    {
        return Payment::class;
    }
}
