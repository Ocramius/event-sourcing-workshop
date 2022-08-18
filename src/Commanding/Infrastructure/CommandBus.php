<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Commanding\Infrastructure;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\Exception\CommandNotHandled;

interface CommandBus
{
    /** @throws CommandNotHandled */
    public function __invoke(Command $command): void;
}
