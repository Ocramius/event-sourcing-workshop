<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming;

use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization\DeSerializeEvent;
use Generator;
use Psl\Json;
use Psl\Type;

use function assert;
use function class_exists;
use function is_a;

/**
 * A simplistic implementation of {@see TraverseEventStream} that:
 *
 * 1. stores a "last seen event" in a table
 * 2. finds the next event after that, and yields it
 * 3. updates the cursor
 * 4. repeats until stream consumed
 */
final class TraverseEventStreamAndSaveStatusInSqlite implements TraverseEventStream
{
    public function __construct(
        private readonly Connection $db,
        private readonly DeSerializeEvent $loadEvent,
    ) {
    }

    public function __invoke(string $name): Generator
    {
        $this->registerCursorIfNotAlreadyRegistered($name);

        $startFrom = $this->getCursorStartingPosition($name);

        while ($eventAndOffset = $this->nextEvent($startFrom)) {
            yield $eventAndOffset['event'];

            $startFrom = $eventAndOffset['no'];

            // Note: we intentionally move the cursor **AFTER** yielding, so that if any
            //       consumer of the generator interrupts processing, we don't consider
            //       the current event as "processed"
            $this->updateSavedCursorPosition($name, $startFrom);
        }
    }

    /** @param non-empty-string $name */
    private function registerCursorIfNotAlreadyRegistered(string $name): void
    {
        $this->db->executeStatement(
            <<<'SQL'
INSERT OR IGNORE INTO event_stream_cursors (name)
    VALUES (:name)
SQL
            ,
            ['name' => $name],
        );
    }

    /**
     * @param non-empty-string $name
     *
     * @return positive-int|0
     */
    private function getCursorStartingPosition(string $name): int
    {
        return Type\union(Type\positive_int(), Type\literal_scalar(0))
            ->assert($this->db->fetchOne(
                <<<'SQL'
SELECT last_seen_event_no
FROM event_stream_cursors
WHERE name = :name
SQL
                ,
                ['name' => $name],
            ));
    }

    /**
     * @param non-empty-string $name
     * @param positive-int     $offset
     */
    private function updateSavedCursorPosition(string $name, int $offset): void
    {
        $this->db->executeStatement(
            <<<'SQL'
UPDATE event_stream_cursors
    SET last_seen_event_no = :offset
    WHERE name = :name
SQL
            ,
            [
                'name'   => $name,
                'offset' => $offset,
            ],
        );
    }

    /**
     * @param positive-int|0 $offset
     *
     * @return array{no: positive-int, event: DomainEvent}| null
     */
    private function nextEvent(int $offset): array|null
    {
        $row = Type\union(
            Type\literal_scalar(false),
            Type\shape([
                'no'                => Type\positive_int(),
                'event_type'        => Type\non_empty_string(),
                'payload'           => Type\non_empty_string(),
            ]),
        )->assert($this->db->fetchAssociative(
            <<<'SQL'
SELECT
    no,
    event_type,
    payload
FROM event_stream
WHERE no > :offset
ORDER BY no ASC
LIMIT 1
SQL
            ,
            ['offset' => $offset],
        ));

        if ($row === false) {
            return null;
        }

        $domainEventClass = $row['event_type'];

        assert(class_exists($domainEventClass) && is_a($domainEventClass, DomainEvent::class, true));

        return [
            'no'    => $row['no'],
            'event' => ($this->loadEvent)(
                $domainEventClass,
                Json\typed(
                    $row['payload'],
                    Type\dict(Type\non_empty_string(), Type\mixed()),
                ),
            ),
        ];
    }
}
