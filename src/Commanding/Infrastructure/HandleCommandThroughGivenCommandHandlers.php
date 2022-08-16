<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Commanding\Infrastructure;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\Exception\CommandNotHandled;

final class HandleCommandThroughGivenCommandHandlers implements CommandBus
{
    /**
     * @param list<CommandHandler<CommandType>> $handlers
     *
     * @template CommandType of Command
     */
    public function __construct(private readonly array $handlers)
    {
    }

    public function __invoke(Command $command): void
    {
        foreach ($this->handlers as $handler) {
            $handledCommand = $handler->handlesCommand();

            if ($command instanceof $handledCommand) {
                $handler($command);

                return;
            }
        }

        throw CommandNotHandled::fromCommandAndConfiguredCommandHandlers($command, $this->handlers);
    }
}
