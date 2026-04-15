<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\ValueObject\Money;

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

    public function testItComparesAndClassifiesAmounts(): void
    {
        $zero = Money::zero();
        $amount = Money::fromCents(25);
        $sameAmount = Money::fromCents(25);

        self::assertTrue($amount->equals($sameAmount));
        self::assertFalse($amount->equals($zero));
        self::assertTrue($amount->isPositive());
        self::assertFalse($zero->isPositive());
        self::assertTrue($zero->isZero());
        self::assertFalse($amount->isZero());
    }
}
