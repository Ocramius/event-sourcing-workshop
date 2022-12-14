<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Domain;

use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-immutable
 * @template-implements AggregateId<Greeting>
 */
final class GreetingId implements AggregateId
{
    private function __construct(private readonly UuidInterface $id)
    {
    }

    public function toString(): string
    {
        return $this->id->toString();
    }

    public function aggregateType(): string
    {
        return Greeting::class;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    /** @psalm-pure */
    public static function fromString(string $id): self
    {
        return new self(Uuid::fromString($id));
    }
}
