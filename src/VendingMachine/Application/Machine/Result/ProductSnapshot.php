<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\Selector;

/**
 * Flat application snapshot of one product entry in the machine.
 */
final readonly class ProductSnapshot
{
    private string $name;
    private Selector $selector;

    public function __construct(
        Selector|string $selector,
        private int $priceCents,
        private int $quantity,
        string $name,
    ) {
        $this->selector = Selector::from($selector);
        $this->name = trim($name);

        if ($this->name === '') {
            throw new InvalidArgumentException('Product snapshot name cannot be empty.');
        }

        if ($this->priceCents <= 0) {
            throw new InvalidArgumentException('Product snapshot price must be greater than zero.');
        }

        if ($this->quantity < 0) {
            throw new InvalidArgumentException('Product snapshot quantity cannot be negative.');
        }
    }

    public function isAvailable(): bool
    {
        return $this->quantity > 0;
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

    public function selector(): Selector
    {
        return $this->selector;
    }
}
