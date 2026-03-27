<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\Coin;
use VendingMachine\Domain\Machine\InsertedCoins;
use VendingMachine\Domain\Machine\Money;

final class CoinStateTest extends TestCase
{
    public function testItBuildsAnEmptyCoinState(): void
    {
        $availableChange = AvailableChange::empty();
        $insertedCoins = InsertedCoins::empty();

        self::assertTrue($availableChange->isEmpty());
        self::assertTrue($insertedCoins->isEmpty());
        self::assertSame([], $availableChange->counts());
        self::assertSame([], $insertedCoins->counts());
        self::assertSame(0, $availableChange->total()->cents());
        self::assertSame(0, $insertedCoins->total()->cents());
    }

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

    public function testItAllocatesExactChangeWithoutRelyingOnAGreedyPick(): void
    {
        $availableChange = AvailableChange::fromCounts([
            25 => 1,
            10 => 3,
        ]);

        $allocatedCoins = $availableChange->allocateChange(Money::fromCents(30));

        self::assertNotNull($allocatedCoins);
        self::assertSame([10 => 3], $allocatedCoins->counts());
    }

    public function testItExposesInsertedCoinStateOperations(): void
    {
        $insertedCoins = InsertedCoins::empty()
            ->addCoin(Coin::TwentyFiveCents)
            ->addCoin(Coin::TenCents);

        self::assertSame([10 => 1, 25 => 1], $insertedCoins->counts());
        self::assertSame(1, $insertedCoins->countFor(Coin::TenCents));
        self::assertSame(35, $insertedCoins->total()->cents());
        self::assertSame([10 => 1, 25 => 1], $insertedCoins->toCoinInventory()->counts());
        self::assertTrue($insertedCoins->clear()->isEmpty());
    }

    public function testItMergesAndRemovesAvailableChange(): void
    {
        $availableChange = AvailableChange::fromCounts([5 => 1, 25 => 1]);
        $updatedChange = $availableChange->addInsertedCoins(InsertedCoins::fromCounts([10 => 2]));
        $remainingChange = $updatedChange->remove($updatedChange->allocateChange(Money::fromCents(20)) ?? throw new \RuntimeException('Change allocation failed.'));

        self::assertSame([5 => 1, 10 => 2, 25 => 1], $updatedChange->counts());
        self::assertSame([5 => 1, 25 => 1], $remainingChange->counts());
        self::assertSame([5 => 1, 10 => 2, 25 => 1], $updatedChange->toCoinInventory()->counts());
    }

    public function testItAllocatesZeroAmountAsEmptyChange(): void
    {
        $allocatedCoins = AvailableChange::fromCounts([25 => 1])->allocateChange(Money::zero());

        self::assertNotNull($allocatedCoins);
        self::assertSame([], $allocatedCoins->counts());
        self::assertTrue($allocatedCoins->isEmpty());
    }

    public function testItReturnsNullWhenExactChangeCannotBeAllocated(): void
    {
        $allocatedCoins = AvailableChange::fromCounts([25 => 1])->allocateChange(Money::fromCents(10));

        self::assertNull($allocatedCoins);
    }

    public function testItRejectsNonIntegerCoinCounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin counts must be integers.');

        AvailableChange::fromCounts([25 => '1']);
    }

    public function testItRejectsInvalidCoinDenominationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin denomination keys must be integer values.');

        AvailableChange::fromCounts(['quarter' => 1]);
    }

    public function testItNormalizesNumericStringCoinDenominationKeys(): void
    {
        $counts = json_decode('{"25":1,"10":2}', true, 512, JSON_THROW_ON_ERROR);
        $availableChange = AvailableChange::fromCounts($counts);

        self::assertSame([10 => 2, 25 => 1], $availableChange->counts());
    }

    public function testItRejectsCoinSubtractionsThatWouldBecomeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin inventory subtraction cannot produce negative counts.');

        AvailableChange::fromCounts([25 => 1])->remove(InsertedCoins::fromCounts([25 => 2])->toCoinInventory());
    }
}
