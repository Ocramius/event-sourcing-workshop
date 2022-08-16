<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\Projection;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProjectionTable;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\DomainEvent\HelloSaid;

/** @psalm-immutable */
final class DateOfLastDispensedGreeting implements DbTableProjectionDefinition
{
    /** @psalm-pure */
    public function tableName(): string
    {
        return 'projection_date_of_last_dispensed_greeting';
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-pure
     * @psalm-suppress MoreSpecificReturnType by design, these closures have a more specific event parameter type than
     *                 just {@see AggregateDomainEvent}
     * @psalm-suppress LessSpecificReturnStatement by design, these closures have a more specific event parameter type
     *                 than just {@see AggregateDomainEvent}
     */
    public function scheduledOperations(): array
    {
        return [
            HelloSaid::class => static function (HelloSaid $event, ProjectionTable $table): void {
                $table->upsert([
                    'last_dispensed_greeting' => $event->raisedAt()
                        ->format(DateTimeImmutable::RFC3339_EXTENDED),
                ]);
            },
        ];
    }
}
