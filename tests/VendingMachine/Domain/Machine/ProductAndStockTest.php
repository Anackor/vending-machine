<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductName;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\ValueObject\Money;
use VendingMachine\Domain\Machine\ValueObject\ProductName;
use VendingMachine\Domain\Machine\ValueObject\Selector;
use VendingMachine\Domain\Machine\ValueObject\StockQuantity;

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
        self::assertSame('Water', $product->productName()->value());
        self::assertSame('water', $product->selector()->value());
        self::assertSame(65, $product->price()->cents());
    }

    public function testItAcceptsProductNameValueObjects(): void
    {
        $name = ProductName::fromString(' Water ');
        $product = new Product(
            Selector::fromString('water'),
            Money::fromCents(65),
            $name,
        );

        self::assertSame($name, $product->productName());
        self::assertSame('Water', $product->name());
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
