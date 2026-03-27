<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Money;

final class MoneyTest extends TestCase
{
    public function testItRejectsNegativeAmounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money cents cannot be negative.');

        Money::fromCents(-1);
    }

    public function testItAddsAndSubtractsAmountsInCents(): void
    {
        $total = Money::fromCents(65)->add(Money::fromCents(100));
        $remaining = $total->subtract(Money::fromCents(25));

        self::assertSame(165, $total->cents());
        self::assertSame(140, $remaining->cents());
    }

    public function testItRejectsSubtractionsThatWouldBecomeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money subtraction cannot produce a negative amount.');

        Money::fromCents(10)->subtract(Money::fromCents(25));
    }
}
