<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

/**
 * Couples a product definition with its current machine stock quantity.
 */
final readonly class ProductStock
{
    public function __construct(
        private Product $product,
        private int $quantity,
    ) {
        if ($this->quantity < 0) {
            throw new InvalidArgumentException('Product stock quantity cannot be negative.');
        }
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function selector(): Selector
    {
        return $this->product->selector();
    }

    public function price(): Money
    {
        return $this->product->price();
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }

    public function decrement(): self
    {
        return $this->withQuantity($this->quantity - 1);
    }

    public function withQuantity(int $quantity): self
    {
        return new self($this->product, $quantity);
    }
}
