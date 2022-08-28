<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain\Command;

use DateTimeImmutable;
use EventSourcingWorkshop\Commanding\Domain\Command;

/** @psalm-immutable */
final class SendNotificationsForLatePayments implements Command
{
    public function __construct(public readonly DateTimeImmutable $deadline)
    {
    }
}
