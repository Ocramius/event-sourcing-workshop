<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Projection;

/**
 * Represents a projection table to be modified (both DDL and DML) at runtime.
 * IMPORTANT: keys used in insert/update/delete statements are NOT quoted by design. *DO NOT* accept
 *            user input for any of this!
 *            Psalm taint analysis may help, but it is **NOT** configured here.
 *
 * Note: event-sourcing projections are not limited to DB tables: it is very much
 *       possible to store to caches, graph databases, CSV reports, XML structures,
 *       etc. We just chose to abstract SQL tables here, for ease of use.
 */
interface ProjectionTable
{
    /** @param non-empty-array<non-empty-string, mixed> $record */
    public function insertIgnore(array $record): void;

    /** @psalm-param non-empty-array<non-empty-string, mixed> $record */
    public function upsert(array $record): void;

    /**
     * @psalm-param non-empty-array<non-empty-string, mixed> $criteria
     * @psalm-param non-empty-array<non-empty-string, mixed> $values
     */
    public function update(array $criteria, array $values): void;

    /** @psalm-param non-empty-array<non-empty-string, mixed> $criteria */
    public function delete(array $criteria): void;

    public function truncate(): void;
}
