<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Unit\Infrastructure\Projection;

use PHPUnit\Framework\MockObject\MockObject;

/** This is an interface that allows creating an {@see callable} that is also a {@see MockObject} */
interface MockableFunction
{
    public function __invoke(): mixed;
}
