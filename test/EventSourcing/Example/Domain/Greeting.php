<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Domain;

use BadMethodCallException;
use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\GoodbyeSaid;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\HelloSaid;

use function assert;
use function Psl\Type\instance_of;
use function Psl\Type\union;

/** @psalm-immutable */
final class Greeting implements Aggregate
{
    private GreetingId $id;
    /** @var positive-int|0 */
    private int $version             = 0;
    private bool $alreadySaidGoodbye = false;

    private function __construct(GreetingId $id)
    {
        $this->id = $id;
    }

    /**
     * @psalm-param non-empty-string $message
     *
     * @psalm-return AggregateChanged<self>
     */
    public static function sayHello(string $message, DateTimeImmutable $date): AggregateChanged
    {
        $instance = new self(GreetingId::generate());

        return AggregateChanged::created($instance->id, [new HelloSaid($date, $instance->id, $message)]);
    }

    /**
     * @psalm-param non-empty-string $message
     *
     * @psalm-return AggregateChanged<self>
     */
    public function sayGoodbye(string $message, DateTimeImmutable $when): AggregateChanged
    {
        if ($this->alreadySaidGoodbye) {
            throw new BadMethodCallException('Goodbye is goodbye - we already said it!');
        }

        return AggregateChanged::changed($this->id, [new GoodbyeSaid($when, $this->id, $message)], $this->version);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-pure
     */
    public static function fromHistory(AggregateId $id, array $history): static
    {
        assert($id instanceof GreetingId);
        $instance = new self($id);

        foreach ($history as $event) {
            /**
             * @psalm-suppress ImpureFunctionCall
             * @psalm-suppress ImpureMethodCall
             */
            $event = union(instance_of(HelloSaid::class), instance_of(GoodbyeSaid::class))
                ->coerce($event);

            $instance = clone $instance;

            $instance->id                 = $event->aggregate();
            $instance->alreadySaidGoodbye = $instance->alreadySaidGoodbye || $event instanceof GoodbyeSaid;
            ++$instance->version;
        }

        return $instance;
    }
}
