<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\ValueObject\AvailableChange;
use VendingMachine\Domain\Machine\ValueObject\InsertedCoins;
use VendingMachine\Domain\Machine\ValueObject\Money;
use VendingMachine\Domain\Machine\ValueObject\Selector;

final class MachineTest extends TestCase
{
    public function testItInitializesTheAggregateFromProductStocksAndCoinState(): void
    {
        $machine = Machine::initialize(
            [
                $this->productStock('water', 'Water', 65, 10),
                $this->productStock('juice', 'Juice', 100, 8),
            ],
            AvailableChange::fromCounts([25 => 4]),
            InsertedCoins::fromCounts([100 => 1, 25 => 1]),
        );

        self::assertCount(2, $machine->productStocks());
        self::assertSame(125, $machine->insertedBalance()->cents());
        self::assertTrue($machine->hasPendingBalance());
        self::assertSame(4, $machine->availableChange()->counts()[25]);
        self::assertSame(8, $machine->productStockFor(Selector::fromString('juice'))?->quantity());
    }

    public function testItRejectsDuplicateSelectorsInTheAggregate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate product selector "water" detected.');

        Machine::initialize([
            $this->productStock('water', 'Water', 65, 10),
            $this->productStock('water', 'Sparkling Water', 65, 5),
        ]);
    }

    public function testItRejectsAnEmptyProductCatalog(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine must define at least one product stock.');

        Machine::initialize([]);
    }

    private function productStock(string $selector, string $name, int $price, int $quantity): ProductStock
    {
        return new ProductStock(
            new Product(
                Selector::fromString($selector),
                Money::fromCents($price),
                $name,
            ),
            $quantity,
        );
    }
}
