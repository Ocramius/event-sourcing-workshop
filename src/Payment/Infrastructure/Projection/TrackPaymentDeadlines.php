<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Infrastructure\Projection;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProjectionTable;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentCompleted;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentDeadlineSet;
use EventSourcingWorkshop\Payment\Domain\DomainEvent\PaymentRequested;

/** @psalm-immutable */
class TrackPaymentDeadlines implements DbTableProjectionDefinition
{
    public function tableName(): string
    {
        return 'projection_pending_payments';
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificReturnType by design, these closures have a more specific event parameter type than
     *                 just {@see DomainEvent}
     * @psalm-suppress LessSpecificReturnStatement by design, these closures have a more specific event parameter type
     *                 than just {@see DomainEvent}
     */
    public function scheduledOperations(): array
    {
        return [
            PaymentRequested::class   => static function (PaymentRequested $event, ProjectionTable $table): void {
                $table->upsert([
                    'payment' => $event->payment->toString(),
                    'debtor'  => $event->debtor->email,
                ]);
            },
            PaymentDeadlineSet::class => static function (PaymentDeadlineSet $event, ProjectionTable $table): void {
                $table->upsert([
                    'payment'  => $event->payment->toString(),
                    'deadline' => $event->deadline->format(DateTimeImmutable::RFC3339_EXTENDED),
                ]);
            },
            PaymentCompleted::class   => static function (PaymentCompleted $event, ProjectionTable $table): void {
                $table->delete(['payment' => $event->payment->toString()]);
            },
        ];
    }
}
