<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command;

use EventSourcingWorkshop\Commanding\Domain\Command;

/** @psalm-immutable */
final class SayHello implements Command
{
    /** @psalm-param non-empty-string $message */
    public function __construct(
        public string $message
    ) {
    }
}
