<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Money;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\Selector;
use VendingMachine\Domain\Machine\StockQuantity;

final class ProductAndStockTest extends TestCase
{
    public function testItBuildsProductsWithNormalizedNamesAndPositivePrices(): void
    {
        $product = new Product(
            Selector::fromString('water'),
            Money::fromCents(65),
            ' Water ',
        );

        self::assertSame('Water', $product->name());
        self::assertSame('water', $product->selector()->value());
        self::assertSame(65, $product->price()->cents());
    }

    public function testItRejectsProductsWithZeroPrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product price must be greater than zero.');

        new Product(
            Selector::fromString('water'),
            Money::zero(),
            'Water',
        );
    }

    public function testItRejectsNegativeStockQuantities(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock quantity cannot be negative.');

        new ProductStock(
            new Product(Selector::fromString('water'), Money::fromCents(65), 'Water'),
            -1,
        );
    }

    public function testItAcceptsStockQuantityValueObjects(): void
    {
        $quantity = StockQuantity::fromInt(3);
        $stock = new ProductStock(
            new Product(Selector::fromString('water'), Money::fromCents(65), 'Water'),
            $quantity,
        );

        self::assertSame($quantity, $stock->stockQuantity());
        self::assertSame(3, $stock->quantity());
    }

    public function testItRejectsProductsWithEmptyNames(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty.');

        new Product(
            Selector::fromString('water'),
            Money::fromCents(65),
            '   ',
        );
    }
}
