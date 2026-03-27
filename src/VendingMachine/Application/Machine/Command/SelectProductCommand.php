<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use InvalidArgumentException;

final readonly class SelectProductCommand
{
    private string $machineId;
    private string $selector;

    public function __construct(
        string $selector,
        string $machineId = 'default',
    ) {
        $this->machineId = self::normalizeMachineId($machineId);
        $this->selector = self::normalizeSelector($selector);
    }

    public function machineId(): string
    {
        return $this->machineId;
    }

    public function selector(): string
    {
        return $this->selector;
    }

    private static function normalizeMachineId(string $machineId): string
    {
        $normalized = strtolower(trim($machineId));

        if ($normalized === '') {
            throw new InvalidArgumentException('Machine id cannot be empty.');
        }

        return $normalized;
    }

    private static function normalizeSelector(string $selector): string
    {
        $normalized = strtolower(trim($selector));

        if ($normalized === '') {
            throw new InvalidArgumentException('Product selector cannot be empty.');
        }

        return $normalized;
    }
}
