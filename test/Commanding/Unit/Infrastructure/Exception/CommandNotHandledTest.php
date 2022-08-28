<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Commanding\Unit\Infrastructure\Exception;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\Commanding\Infrastructure\Exception\CommandNotHandled;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\Commanding\Infrastructure\Exception\CommandNotHandled */
final class CommandNotHandledTest extends TestCase
{
    public function testExceptionMessageFormatForUnhandledCommand(): void
    {
        $command1 = $this->getMockBuilder(Command::class)
            ->setMockClassName('CommandNotHandledTestCommand1')
            ->getMock();
        $command2 = $this->getMockBuilder(Command::class)
            ->setMockClassName('CommandNotHandledTestCommand2')
            ->getMock();
        $command3 = $this->getMockBuilder(Command::class)
            ->setMockClassName('CommandNotHandledTestCommand3')
            ->getMock();

        $commandHandler1 = $this->getMockBuilder(CommandHandler::class)
            ->setMockClassName('CommandNotHandledTestCommandHandler1')
            ->getMock();
        $commandHandler2 = $this->getMockBuilder(CommandHandler::class)
            ->setMockClassName('CommandNotHandledTestCommandHandler2')
            ->getMock();

        $commandHandler1->method('handlesCommand')
            ->willReturn($command1::class);
        $commandHandler2->method('handlesCommand')
            ->willReturn($command2::class);

        $exception = CommandNotHandled::fromCommandAndConfiguredCommandHandlers(
            $command3,
            [$commandHandler1, $commandHandler2],
        );

        self::assertSame($command3, $exception->command);
        self::assertSame(
            <<<'MESSAGE'
Could not handle command of type "CommandNotHandledTestCommand3".
Configured handlers:
{
    "CommandNotHandledTestCommand1": "CommandNotHandledTestCommandHandler1",
    "CommandNotHandledTestCommand2": "CommandNotHandledTestCommandHandler2"
}
MESSAGE
            ,
            $exception->getMessage(),
        );
    }
}
