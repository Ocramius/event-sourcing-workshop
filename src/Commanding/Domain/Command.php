<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Commanding\Domain;

/**
 * A command is an immutable payload that expresses the intent of performing an operation on the system.
 * Its API can vary greatly from implementation to implementation, but it must be immutable.
 *
 * @psalm-immutable
 */
interface Command
{
}
