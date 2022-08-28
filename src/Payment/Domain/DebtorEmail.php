<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Domain;

use InvalidArgumentException;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

/** @psalm-immutable */
final class DebtorEmail
{
    /** @param non-empty-string $email */
    public function __construct(public readonly string $email)
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Provided email address "' . $email . '" is not valid');
        }
    }
}
