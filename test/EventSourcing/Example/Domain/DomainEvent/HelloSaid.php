<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Greeting;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\GreetingId;

/**
 * @psalm-immutable
 * @template-implements AggregateDomainEvent<Greeting>
 */
final class HelloSaid implements AggregateDomainEvent
{
    /** @psalm-param non-empty-string $message */
    public function __construct(
        public readonly DateTimeImmutable $raisedAt,
        public readonly GreetingId        $greeting,
        public readonly string            $message
    ) {
    }

    public function aggregate(): GreetingId
    {
        return $this->greeting;
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
            'greeting' => $this->greeting->toString(),
            'message'  => $this->message,
        ];
    }

    /**
     * Note: this is not pure because we use {@see \DateTimeImmutable::__construct()},
     * but you may use an {@see Clock} upfront in your own code.
     *
     * @psalm-param non-empty-string $message
     *
     * @psalm-pure
     */
    public static function raise(GreetingId $greeting, string $message, DateTimeImmutable $date): self
    {
        return new self($date, $greeting, $message);
    }
}
