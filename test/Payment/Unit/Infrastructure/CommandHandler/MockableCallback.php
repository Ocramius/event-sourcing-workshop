<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Payment\Unit\Infrastructure\CommandHandler;

interface MockableCallback
{
    public function __invoke(string $message): void;
}
