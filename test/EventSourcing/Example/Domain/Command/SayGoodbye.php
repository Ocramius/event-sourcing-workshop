<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\GreetingId;

/** @psalm-immutable */
final class SayGoodbye implements Command
{
    /** @psalm-param non-empty-string $message */
    public function __construct(
        public readonly GreetingId $greeting,
        public readonly string $message,
    ) {
    }
}
