<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Coin;
use VendingMachine\Domain\Machine\Selector;

final class CoinAndSelectorTest extends TestCase
{
    public function testItBuildsSupportedCoinsFromCents(): void
    {
        $coin = Coin::fromCents(100);

        self::assertSame(Coin::OneHundredCents, $coin);
        self::assertSame(100, $coin->money()->cents());
    }

    public function testItRejectsUnsupportedCoinDenominations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported coin denomination "50".');

        Coin::fromCents(50);
    }

    public function testItNormalizesSelectors(): void
    {
        $selector = Selector::fromString('  WATER ');

        self::assertSame('water', $selector->value());
    }

    public function testItRejectsInvalidSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Selector "water!" is invalid.');

        Selector::fromString('water!');
    }
}
