<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

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

    public function quantity(): int
    {
        return $this->quantity;
    }
}
