<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\Selector;
use VendingMachine\Domain\Machine\StockQuantity;

/**
 * Flat application snapshot of one product entry in the machine.
 */
final readonly class ProductSnapshot
{
    private string $name;
    private StockQuantity $quantity;
    private Selector $selector;

    public function __construct(
        Selector|string $selector,
        private int $priceCents,
        StockQuantity|int $quantity,
        string $name,
    ) {
        $this->selector = Selector::from($selector);
        $this->quantity = StockQuantity::from($quantity);
        $this->name = trim($name);

        if ($this->name === '') {
            throw new InvalidArgumentException('Product snapshot name cannot be empty.');
        }

        if ($this->priceCents <= 0) {
            throw new InvalidArgumentException('Product snapshot price must be greater than zero.');
        }
    }

    public function isAvailable(): bool
    {
        return $this->quantity->isAvailable();
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
        return $this->quantity->value();
    }

    public function stockQuantity(): StockQuantity
    {
        return $this->quantity;
    }

    public function selector(): Selector
    {
        return $this->selector;
    }
}
