<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain;

use EventSourcingWorkshop\Commanding\Domain\Command;

/**
 * @psalm-template HandledDomainEvent of DomainEvent
 * @psalm-immutable
 *
 * Policies are meant to work as a 'given this then that' to trigger changes based on given events.
 */
interface Policy
{
    /**
     * @psalm-param HandledDomainEvent $event
     *
     * @psalm-return list<Command>
     */
    public function __invoke(DomainEvent $event): array;

    /** @psalm-return class-string<HandledDomainEvent> */
    public function supportedDomainEvent(): string;
}
