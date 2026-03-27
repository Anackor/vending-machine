<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

final readonly class Product
{
    private string $name;

    public function __construct(
        private Selector $selector,
        private Money $price,
        string $name,
    ) {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if (!$this->price->isPositive()) {
            throw new InvalidArgumentException('Product price must be greater than zero.');
        }

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

    public function price(): Money
    {
        return $this->price;
    }
}
