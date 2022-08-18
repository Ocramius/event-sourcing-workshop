<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Asset;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/** @psalm-immutable */
final class DomainEvent3 implements DomainEvent
{
    /** @psalm-param array<non-empty-string, string|int|bool|float|null> $data */
    public function __construct(
        private readonly DateTimeImmutable $raisedAt,
        private readonly array $data
    ) {
    }

    public function raisedAt(): DateTimeImmutable
    {
        return $this->raisedAt;
    }

    /** {@inheritDoc} */
    public function toArray(): array
    {
        return [
            'raisedAt' => $this->raisedAt->format(DateTimeImmutable::RFC3339_EXTENDED),
            'data'     => $this->data,
        ];
    }

    public static function dummy(): self
    {
        return new self(new DateTimeImmutable(), []);
    }
}
