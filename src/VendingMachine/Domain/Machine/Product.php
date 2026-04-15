<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

/**
 * Immutable definition of a vendable product in the catalog.
 */
final readonly class Product
{
    private ProductName $name;

    public function __construct(
        private Selector $selector,
        private Money $price,
        ProductName|string $name,
    ) {
        $this->name = ProductName::from($name);

        if (!$this->price->isPositive()) {
            throw new InvalidArgumentException('Product price must be greater than zero.');
        }
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

    public function price(): Money
    {
        return $this->price;
    }
}
