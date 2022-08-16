<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager;

use EventSourcingWorkshop\Commanding\Infrastructure\CommandBus;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStream;

/**
 * This class is supposed to be used as an entry-point, not as a dependency.
 *
 * Its purpose is to take event stream events, pass them through configured policies,
 * and forward resulting commands to the command bus.
 */
final class ProcessPolicies
{
    /** @param list<Policy> $policies */
    public function __construct(
        private readonly array               $policies,
        private readonly CommandBus          $commandBus,
        private readonly TraverseEventStream $traverseEventStream
    ) {
    }

    public function __invoke(): void
    {
        foreach (($this->traverseEventStream)(self::class) as $event) {
            $this->handleEvent($event);
        }
    }

    private function handleEvent(DomainEvent $event): void
    {
        foreach ($this->policies as $policy) {
            $handledEventType = $policy->supportedDomainEvent();

            if (! $event instanceof $handledEventType) {
                continue;
            }

            $commands = $policy($event);

            array_walk($commands, $this->commandBus);
        }
    }
}
