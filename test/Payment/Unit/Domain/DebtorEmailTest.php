<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Payment\Unit\Domain;

use EventSourcingWorkshop\Payment\Domain\DebtorEmail;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\Payment\Domain\DebtorEmail */
final class DebtorEmailTest extends TestCase
{
    /**
     * @param non-empty-string $email
     *
     * @dataProvider validAddresses
     */
    public function testValidAddress(string $email): void
    {
        self::assertSame($email, (new DebtorEmail($email))->email);
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public function validAddresses(): array
    {
        return [
            ['me@example.com'],
            ['me@test.localhost'],
            ['foo@bar.baz'],
        ];
    }

    /**
     * @param non-empty-string $email
     *
     * @dataProvider invalidAddresses
     */
    public function testInvalidAddress(string $email): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided email address "' . $email . '" is not valid');

        new DebtorEmail($email);
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public function invalidAddresses(): array
    {
        return [
            [' '],
            ['foo'],
            ['foo@'],
            ['foo @ bar'],
        ];
    }
}
