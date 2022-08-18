<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Asset;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProjectionTable;
use Webmozart\Assert\Assert;

/** @psalm-immutable */
final class DummyDbTableProjectionDefinition implements DbTableProjectionDefinition
{
    /** @psalm-pure */
    public function tableName(): string
    {
        return 'dummy_table';
    }

    /**
     * @psalm-pure
     *
     * {@inheritDoc}
     */
    public function scheduledOperations(): array
    {
        return [
            DomainEvent1::class => static function (DomainEvent $event, ProjectionTable $table): void {
                $data = $event->toArray();
                Assert::isNonEmptyMap($data);

                $table->upsert($data);
            },
            DomainEvent2::class => static function (DomainEvent $event, ProjectionTable $table): void {
                $data = $event->toArray();
                Assert::isNonEmptyMap($data);

                $table->upsert($data);
            },
        ];
    }
}
