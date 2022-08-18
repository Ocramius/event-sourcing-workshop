<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Domain\Policy;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command\SayGoodbye;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\HelloSaid;

/**
 * @template-implements Policy<HelloSaid>
 * @psalm-immutable
 *
 * This policy demonstrates how we can automatically fire a {@see SayGoodbye} whenever an {@see HelloSaid} is raised.
 * @psalm-suppress UnusedClass this class is only wired in dependency-injection: in a real-world scenario,
 *                 you should unit-/integration-test it!
 */
final class WhenHelloSaidThenSayGoodbye implements Policy
{
    /** {@inheritDoc} */
    public function __invoke(DomainEvent $event): array
    {
        return [new SayGoodbye($event->aggregate(), 'goodbye - ' . $event->message)];
    }

    public function supportedDomainEvent(): string
    {
        return HelloSaid::class;
    }
}
