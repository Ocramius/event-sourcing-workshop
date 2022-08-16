<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

interface DeSerializeEvent
{
    /**
     * @TODO do we want to enforce `class-string<RequestedEventType>`? That forces translation in upstream.
     *
     * @template RequestedEventType of DomainEvent
     *
     * @param class-string<RequestedEventType> $type
     * @param array<non-empty-string, mixed>   $payload
     *
     * @return RequestedEventType
     */
    public function __invoke(string $type, array $payload): DomainEvent;
}
