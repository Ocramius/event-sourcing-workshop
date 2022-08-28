<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\TimeTracking\Unit\Domain;

use EventSourcingWorkshop\TimeTracking\Domain\Date;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;

/** @covers \EventSourcingWorkshop\TimeTracking\Domain\Date */
final class DateTest extends TestCase
{
    /**
     * @param non-empty-string $date
     *
     * @dataProvider validDates
     */
    public function testValidDates(string $date): void
    {
        $instance = new Date($date);

        self::assertSame($date, $instance->toString());
        self::assertSame($date, $instance->date->format('Y-m-d'));
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public function validDates(): array
    {
        return [
            ['2022-08-28'],
            ['2022-01-01'],
            ['2022-02-28'],
        ];
    }

    /**
     * @param non-empty-string $date
     *
     * @dataProvider invalidDates
     */
    public function testInvalidDates(string $date): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Given date ' . $date . ' is not valid according to the "Y-m-d" format');

        new Date($date);
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public function invalidDates(): array
    {
        return [
            ['2022-08-32'],
            ['2022-8-30'],
            ['2022-01-00'],
            ['2022'],
            ['2022-02-29'],
        ];
    }
}
