<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Payment\Unit\Domain;

use EventSourcingWorkshop\Payment\Domain\Amount;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\Payment\Domain\Amount */
final class AmountTest extends TestCase
{
    public function testAmountMatches(): void
    {
        self::assertTrue((new Amount(123))->matches(new Amount(123)));
        self::assertFalse((new Amount(123))->matches(new Amount(124)));
        self::assertFalse((new Amount(123))->matches(new Amount(122)));
    }
}
