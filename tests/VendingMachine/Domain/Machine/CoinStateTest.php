<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\Coin;
use VendingMachine\Domain\Machine\InsertedCoins;

final class CoinStateTest extends TestCase
{
    public function testItCalculatesAvailableChangeTotalsFromCoinCounts(): void
    {
        $availableChange = AvailableChange::fromCounts([
            5 => 2,
            25 => 1,
            100 => 1,
        ]);

        self::assertSame(135, $availableChange->total()->cents());
        self::assertSame(2, $availableChange->countFor(Coin::FiveCents));
        self::assertFalse($availableChange->isEmpty());
    }

    public function testItRejectsUnsupportedCoinDenominationsInCoinState(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported coin denomination "50".');

        InsertedCoins::fromCounts([50 => 1]);
    }

    public function testItRejectsNegativeCoinCounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin counts cannot be negative.');

        AvailableChange::fromCounts([25 => -1]);
    }
}
