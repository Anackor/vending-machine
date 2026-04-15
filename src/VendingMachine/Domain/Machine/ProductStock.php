<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

/**
 * Couples a product definition with its current machine stock quantity.
 */
final readonly class ProductStock
{
    private StockQuantity $quantity;

    public function __construct(
        private Product $product,
        StockQuantity|int $quantity,
    ) {
        $this->quantity = StockQuantity::from($quantity);
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
        return $this->quantity->value();
    }

    public function stockQuantity(): StockQuantity
    {
        return $this->quantity;
    }

    public function isAvailable(): bool
    {
        return $this->quantity->isAvailable();
    }

    public function decrement(): self
    {
        return $this->withQuantity($this->quantity->decrement());
    }

    public function withQuantity(StockQuantity|int $quantity): self
    {
        return new self($this->product, $quantity);
    }
}
