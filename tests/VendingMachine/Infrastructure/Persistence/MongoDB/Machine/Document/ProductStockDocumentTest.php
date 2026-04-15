<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\StockQuantity;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document\ProductStockDocument;

final class ProductStockDocumentTest extends TestCase
{
    public function testItBuildsAProductStockDocument(): void
    {
        $document = new ProductStockDocument('water', 65, 3, 'Water');

        self::assertSame('water', $document->selector()->value());
        self::assertSame('Water', $document->name());
        self::assertSame(65, $document->priceCents());
        self::assertSame(3, $document->quantity());
    }

    public function testItAcceptsStockQuantityValueObjects(): void
    {
        $quantity = StockQuantity::fromInt(3);
        $document = new ProductStockDocument('water', 65, $quantity, 'Water');

        self::assertSame($quantity, $document->stockQuantity());
        self::assertSame(3, $document->quantity());
    }

    public function testItRejectsInvalidPersistedProductShapes(): void
    {
        try {
            new ProductStockDocument('   ', 65, 1, 'Water');
            self::fail('The document should reject empty selectors.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Selector cannot be empty.', $exception->getMessage());
        }

        try {
            new ProductStockDocument('water', 0, 1, 'Water');
            self::fail('The document should reject non-positive prices.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted product price must be greater than zero.', $exception->getMessage());
        }

        try {
            new ProductStockDocument('water', 65, -1, 'Water');
            self::fail('The document should reject negative quantities.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Stock quantity cannot be negative.', $exception->getMessage());
        }

        try {
            new ProductStockDocument('water', 65, 1, '   ');
            self::fail('The document should reject empty names.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted product name cannot be empty.', $exception->getMessage());
        }
    }
}
