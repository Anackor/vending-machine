<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;

final readonly class ProductStockDocument
{
    private string $selector;
    private string $name;

    public function __construct(
        string $selector,
        private int $priceCents,
        private int $quantity,
        string $name,
    ) {
        $selector = trim($selector);
        $name = trim($name);

        if ($selector === '') {
            throw new InvalidArgumentException('Persisted product selector cannot be empty.');
        }

        if ($name === '') {
            throw new InvalidArgumentException('Persisted product name cannot be empty.');
        }

        if ($this->priceCents <= 0) {
            throw new InvalidArgumentException('Persisted product price must be greater than zero.');
        }

        if ($this->quantity < 0) {
            throw new InvalidArgumentException('Persisted product quantity cannot be negative.');
        }

        $this->selector = $selector;
        $this->name = $name;
    }

    public function selector(): string
    {
        return $this->selector;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function priceCents(): int
    {
        return $this->priceCents;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
