<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TemperatureTracking\Domain;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;

/**
 * @template-implements Policy<TemperatureRecorded>
 * @psalm-immutable
 */
final class WhenTemperatureBelowZeroSendAlert implements Policy
{
    public function __invoke(DomainEvent $event): array
    {
        if ($event->celsius > 0) {
            return [];
        }

        return [new SendTemperatureBelowZeroAlert($event->location, $event->celsius)];
    }

    public function supportedDomainEvent(): string
    {
        return TemperatureRecorded::class;
    }
}
