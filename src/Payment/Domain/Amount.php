<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain;

/** @psalm-immutable */
final class Amount
{
    /** @param positive-int $amount */
    public function __construct(public readonly int $amount)
    {
    }

    public function matches(Amount $other): bool
    {
        return $this->amount === $other->amount;
    }
}
