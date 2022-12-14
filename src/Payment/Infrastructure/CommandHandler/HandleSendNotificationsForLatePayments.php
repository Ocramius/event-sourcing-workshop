<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Payment\Infrastructure\CommandHandler;

use Closure;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\Payment\Domain\Command\SendNotificationsForLatePayments;
use Psl\Type;

use function error_log;

/** @template-implements CommandHandler<SendNotificationsForLatePayments> */
final class HandleSendNotificationsForLatePayments implements CommandHandler
{
    /** @var Closure(string): void */
    private readonly Closure $printOutput;

    /** @param callable(string): void $printOutput */
    public function __construct(private readonly Connection $db, callable|null $printOutput = null)
    {
        $this->printOutput = Closure::fromCallable($printOutput ?? error_log(...));
    }

    /** {@inheritDoc} */
    public function __invoke(Command $command): void
    {
        $latePayments = Type\vec(Type\shape([
            'payment' => Type\non_empty_string(),
            'debtor'  => Type\non_empty_string(),
        ]))->assert($this->db->fetchAllAssociative(
            <<<'SQL'
SELECT
    payment,
    debtor
FROM projection_pending_payments
WHERE debtor IS NOT NULL
AND deadline < :deadline
SQL,
            ['deadline' => $command->deadline->format(DateTimeImmutable::RFC3339_EXTENDED)],
        ));

        foreach ($latePayments as $latePayment) {
            ($this->printOutput)(
                'Notifying ' . $latePayment['debtor'] . ' of late payment for ' . $latePayment['payment'],
            );
        }
    }

    public function handlesCommand(): string
    {
        return SendNotificationsForLatePayments::class;
    }
}
