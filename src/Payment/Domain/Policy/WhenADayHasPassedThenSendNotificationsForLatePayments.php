<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain\Policy;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;
use EventSourcingWorkshop\Payment\Domain\Command\SendNotificationsForLatePayments;
use EventSourcingWorkshop\TimeTracking\Domain\DomainEvent\ADayHasPassed;

/**
 * @psalm-immutable
 * @template-implements Policy<ADayHasPassed>
 */
final class WhenADayHasPassedThenSendNotificationsForLatePayments implements Policy
{
    /** {@inheritDoc} */
    public function __invoke(DomainEvent $event): array
    {
        return [new SendNotificationsForLatePayments($event->newDayDate->date)];
    }

    public function supportedDomainEvent(): string
    {
        return ADayHasPassed::class;
    }
}
