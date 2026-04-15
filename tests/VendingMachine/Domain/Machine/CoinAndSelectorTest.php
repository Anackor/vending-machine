<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Coin;
use VendingMachine\Domain\Machine\MachineId;
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

    public function testItRejectsEmptySelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Selector cannot be empty.');

        Selector::fromString('   ');
    }

    public function testItComparesAndStringifiesSelectors(): void
    {
        $selector = Selector::fromString('water');
        $sameSelector = Selector::fromString('WATER');
        $differentSelector = Selector::fromString('juice');

        self::assertTrue($selector->equals($sameSelector));
        self::assertFalse($selector->equals($differentSelector));
        self::assertSame('water', (string) $selector);
    }

    public function testItNormalizesMachineIds(): void
    {
        $machineId = MachineId::fromString('  LOBBY-01 ');

        self::assertSame('lobby-01', $machineId->value());
        self::assertSame('default', MachineId::default()->value());
    }

    public function testItRejectsEmptyMachineIds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        MachineId::fromString('   ');
    }

    public function testItComparesAndStringifiesMachineIds(): void
    {
        $machineId = MachineId::fromString('default');
        $sameMachineId = MachineId::fromString(' DEFAULT ');
        $differentMachineId = MachineId::fromString('lobby');

        self::assertTrue($machineId->equals($sameMachineId));
        self::assertFalse($machineId->equals($differentMachineId));
        self::assertSame('default', (string) $machineId);
        self::assertSame($machineId, MachineId::from($machineId));
    }
}
