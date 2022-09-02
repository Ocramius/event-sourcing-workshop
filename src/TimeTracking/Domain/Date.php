<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TimeTracking\Domain;

use DateTimeImmutable;
use Psl\Exception\InvariantViolationException;

use function Psl\invariant;

/** @psalm-immutable */
final class Date
{
    public readonly DateTimeImmutable $date;

    /**
     * @psalm-param non-empty-string $date
     *
     * @throws InvariantViolationException
     */
    public function __construct(string $date)
    {
        $dateAsObject = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        invariant(
            $dateAsObject !== false
            && $dateAsObject->format('Y-m-d') === $date,
            'Given date ' . $date . ' is not valid according to the "Y-m-d" format',
        );

        $this->date = $dateAsObject;
    }

    /** @return non-empty-string */
    public function toString(): string
    {
        return $this->date->format('Y-m-d');
    }
}
