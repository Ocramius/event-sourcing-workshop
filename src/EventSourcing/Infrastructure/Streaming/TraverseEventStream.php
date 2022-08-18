<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming;

use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/**
 * This abstraction is responsible for taking our event stream, and creating unique, persistent,
 * named cursors on top of it, so that multiple subsequent iterations over the stream do not
 * repeat processing of past events.
 *
 * In pseudo-code, this is:
 *
 * ```php
 * for ($i = getLastIndex($name); $i < countOfEventsInStream(); $i++) {
 *     yield getEvent($i);
 * }
 *
 * saveLastIndex($name, $i)
 * ```
 */
interface TraverseEventStream
{
    /**
     * @param non-empty-string $name unique name of this process, used to identify whether
     *                               we already previously processed this stream, and whether
     *                               to continue from where we left off.
     *
     * @return iterable<int, DomainEvent>
     */
    public function __invoke(string $name): iterable;
}
