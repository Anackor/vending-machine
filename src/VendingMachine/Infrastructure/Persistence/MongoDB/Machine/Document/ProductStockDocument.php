<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\Selector;

/**
 * Persistence DTO for one product entry inside the machine MongoDB document.
 */
final readonly class ProductStockDocument
{
    private Selector $selector;
    private string $name;

    public function __construct(
        Selector|string $selector,
        private int $priceCents,
        private int $quantity,
        string $name,
    ) {
        $selector = Selector::from($selector);
        $name = trim($name);

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

    public function selector(): Selector
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
