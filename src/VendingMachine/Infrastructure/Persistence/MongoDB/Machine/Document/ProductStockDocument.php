<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\ValueObject\ProductName;
use VendingMachine\Domain\Machine\ValueObject\Selector;
use VendingMachine\Domain\Machine\ValueObject\StockQuantity;

/**
 * Persistence DTO for one product entry inside the machine MongoDB document.
 */
final readonly class ProductStockDocument
{
    private Selector $selector;
    private StockQuantity $quantity;
    private ProductName $name;

    public function __construct(
        Selector|string $selector,
        private int $priceCents,
        StockQuantity|int $quantity,
        ProductName|string $name,
    ) {
        $selector = Selector::from($selector);
        $quantity = StockQuantity::from($quantity);
        $name = ProductName::from($name);

        if ($this->priceCents <= 0) {
            throw new InvalidArgumentException('Persisted product price must be greater than zero.');
        }

        $this->selector = $selector;
        $this->quantity = $quantity;
        $this->name = $name;
    }

    public function selector(): Selector
    {
        return $this->selector;
    }

    public function name(): string
    {
        return $this->name->value();
    }

    public function productName(): ProductName
    {
        return $this->name;
    }

    public function priceCents(): int
    {
        return $this->priceCents;
    }

    public function quantity(): int
    {
        return $this->quantity->value();
    }

    public function stockQuantity(): StockQuantity
    {
        return $this->quantity;
    }
}
