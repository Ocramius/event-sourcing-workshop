<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateChanged;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateDomainEvent;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateRepository;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use Generator;
use Psl\Json;
use Psl\Type;
use function Psl\invariant;

final class SQLiteEventStore implements EventStore
{
    public function __construct(
        private readonly Connection       $db,
        private readonly DeSerializeEvent $deSerializeEvent
    ) {
    }

    public function save(DomainEvent ...$domainEvent): void
    {
        $this->db->transactional(function () use ($domainEvent): void {
            foreach ($domainEvent as $event) {
                invariant(
                    ! $event instanceof AggregateDomainEvent,
                    'Please save ' . AggregateChanged::class
                    . ' instances through ' . AggregateRepository::class . ' instead'
                );

                $this->db->insert(
                    'event_stream',
                    [
                        'event_type'        => get_class($event),
                        'time_of_recording' => $event->raisedAt()
                            ->format(DateTimeImmutable::RFC3339_EXTENDED),
                        'payload'           => Json\encode($event->toArray()),
                    ]
                );
            }
        });
    }

    public function stream(array $filter): Generator
    {
        $parameters     = [];
        $parameterTypes = [];

        $whereClauses = [];

        if (array_key_exists('no', $filter)) {
            $whereClauses[]       = 'no IN (:no)';
            $parameters['no']     = $filter['no'];
            $parameterTypes['no'] = Connection::PARAM_INT_ARRAY;
        }

        if (array_key_exists('no_after', $filter)) {
            $whereClauses[]             = 'no > :no_after';
            $parameters['no_after']     = $filter['no_after'];
            $parameterTypes['no_after'] = Types::INTEGER;
        }

        if (array_key_exists('event_type', $filter)) {
            $whereClauses[]               = 'event_type IN (:event_type)';
            $parameters['event_type']     = $filter['event_type'];
            $parameterTypes['event_type'] = Connection::PARAM_STR_ARRAY;
        }

        if (array_key_exists('time_of_recording_after', $filter)) {
            $whereClauses[]                            = 'time_of_recording > :time_of_recording_after';
            $parameters['time_of_recording_after']     = $filter['time_of_recording_after']->format('Y-m-d H:i:s.u');
            $parameterTypes['time_of_recording_after'] = Types::STRING;
        }

        if (array_key_exists('time_of_recording_before', $filter)) {
            $whereClauses[]                             = 'time_of_recording < :time_of_recording_before';
            $parameters['time_of_recording_before']     = $filter['time_of_recording_before']->format('Y-m-d H:i:s.u');
            $parameterTypes['time_of_recording_before'] = Types::STRING;
        }

        $type     = Type\union(Type\literal_scalar(false), Type\shape([
            'event_type' => Type\non_empty_string(),
            'payload'    => Type\non_empty_string(),
        ]));
        $jsonType = Type\dict(Type\non_empty_string(), Type\mixed());
        $result   = $this->db->executeQuery(
            'SELECT event_type, payload FROM event_stream'
            . (
            $whereClauses === []
                ? ''
                : ' WHERE ' . implode(' AND ', $whereClauses)
            )
            . ' ORDER BY no ASC',
            $parameters,
            $parameterTypes
        );

        while ($row = $type->assert($result->fetchAssociative())) {
            $eventType = $row['event_type'];

            invariant(
                is_a($eventType, DomainEvent::class, true),
                'Event type must be a subtype of ' . DomainEvent::class
            );

            yield ($this->deSerializeEvent)($eventType, Json\typed($row['payload'], $jsonType));
        }
    }
}