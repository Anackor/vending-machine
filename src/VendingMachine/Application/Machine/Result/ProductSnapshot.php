<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

use InvalidArgumentException;

/**
 * Flat application snapshot of one product entry in the machine.
 */
final readonly class ProductSnapshot
{
    private string $name;
    private string $selector;

    public function __construct(
        string $selector,
        private int $priceCents,
        private int $quantity,
        string $name,
    ) {
        $this->selector = self::normalizeSelector($selector);
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

    public function selector(): string
    {
        return $this->selector;
    }

    private static function normalizeSelector(string $selector): string
    {
        $normalized = strtolower(trim($selector));

        if ($normalized === '') {
            throw new InvalidArgumentException('Product snapshot selector cannot be empty.');
        }

        return $normalized;
    }
}
