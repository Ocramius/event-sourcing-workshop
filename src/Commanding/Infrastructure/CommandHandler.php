<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Commanding\Infrastructure;

use EventSourcingWorkshop\Commanding\Domain\Command;

/** @template HandledCommand of Command */
interface CommandHandler
{
    /** @param HandledCommand $command */
    public function __invoke(Command $command): void;

    /** @return class-string<HandledCommand> */
    public function handlesCommand(): string;
}
