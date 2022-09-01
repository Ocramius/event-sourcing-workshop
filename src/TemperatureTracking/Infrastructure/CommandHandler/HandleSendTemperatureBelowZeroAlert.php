<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TemperatureTracking\Infrastructure\CommandHandler;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\TemperatureTracking\Domain\SendTemperatureBelowZeroAlert;

use function error_log;

/** @template-implements CommandHandler<SendTemperatureBelowZeroAlert> */
final class HandleSendTemperatureBelowZeroAlert implements CommandHandler
{
    public function __invoke(Command $command): void
    {
        error_log('Temperature below zero recorded in ' . $command->location . ' (' . $command->celsius . ')');
    }

    public function handlesCommand(): string
    {
        return SendTemperatureBelowZeroAlert::class;
    }
}
