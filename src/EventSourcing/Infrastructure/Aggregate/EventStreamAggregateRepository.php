<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Aggregate;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Aggregate;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateId;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateRepository;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\Exception\AggregateNotFound;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use Webmozart\Assert\Assert;
use function Psl\Json\typed;
use function Psl\Type\dict;
use function Psl\Type\mixed;
use function Psl\Type\non_empty_string;

/** @template-implements AggregateRepository<Aggregate> */
final class EventStreamAggregateRepository implements AggregateRepository
{
    public function __construct(
        private readonly Connection $db,
        private readonly DeSerializeEvent $loadEvent
    ) {
    }

    public function save(AggregateChanged $changed): void
    {
        $this->db->transactional(function () use ($changed) {
            $version = $changed->previousVersion;

            foreach ($changed->raisedEvents as $event) {
                ++$version;

                $this->db->insert(
                    'event_stream',
                    [
                        'event_type'             => get_class($event),
                        'aggregate_root_type'    => $event->aggregate()
                            ->aggregateType(),
                        'aggregate_root_id'      => $event->aggregate()
                            ->toUuid()
                            ->getBytes(),
                        'aggregate_root_version' => $version,
                        'time_of_recording'      => $event->raisedAt()
                            ->format('Y-m-d H:i:s.u'),
                        'payload'                => $event->toArray(),
                    ],
                    [
                        'event_type'             => Types::STRING,
                        'aggregate_root_type'    => Types::STRING,
                        'aggregate_root_id'      => Types::STRING,
                        'aggregate_root_version' => Types::INTEGER,
                        'time_of_recording'      => Types::STRING,
                        'payload'                => Types::JSON,
                    ]
                );
            }
        });
    }

    public function get(AggregateId $id): Aggregate
    {
        /**
         * @psalm-var list<array{
         *     event_type: class-string,
         *     time_of_recording: non-empty-string,
         *     payload: non-empty-string
         * }>
         */
        $events = $this->db->fetchAllAssociative(<<<'SQL'
SELECT
    event_type,
    time_of_recording,
    payload
FROM event_stream
WHERE aggregate_root_id = :aggregateId 
ORDER BY aggregate_root_version ASC
SQL
            ,
            [
                'aggregateId' => $id->toUuid()
                    ->getBytes(),
            ],
            [
                'aggregateId' => Types::STRING,
            ]
        );

        if ([] === $events) {
            throw AggregateNotFound::forAggregateId($id);
        }

        /** @psalm-var AggregateDomainEvent<Aggregate> $events */
        $events = array_map(
            function (array $row): AggregateDomainEvent {
                Assert::implementsInterface($row['event_type'], AggregateDomainEvent::class);

                return ($this->loadEvent)(
                    $row['event_type'],
                    typed($row['payload'], dict(non_empty_string(), mixed()))
                );
            },
            $events
        );

        /**
         * @psalm-suppress InvalidArgument it is impossible to reconcile AggregateDomainEvent<Aggregate&static>>
         *                 with AggregateDomainEvent<Aggregate>> here, because Aggregate may have multiple
         *                 levels of inheritance here: we'll have to roll with a suppression.
         */
        return $id->aggregateType()::fromHistory($id, $events);
    }
}
