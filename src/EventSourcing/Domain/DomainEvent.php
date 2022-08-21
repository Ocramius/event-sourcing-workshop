<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain;

use DateTimeImmutable;

/** @psalm-immutable */
interface DomainEvent
{
    public function raisedAt(): DateTimeImmutable;

    /**
     * Note: for the purposes of this workshop, it is endorsed that these array keys correspond
     *       to constructor parameter names. If that's not the case, there will be fun/trouble :-D
     *       That's because event de-serialization is currently based off simplistic assumptions,
     *       which may be fixed with a more elaborate serializer.
     *
     * @return array<non-empty-string, string|int|bool|float|array|null>
     */
    public function toArray(): array;
}
