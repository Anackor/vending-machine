<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\MachineId;

/**
 * Carries the input required to select a product from the machine.
 */
final readonly class SelectProductCommand
{
    private MachineId $machineId;
    private string $selector;

    public function __construct(
        string $selector,
        MachineId|string $machineId = 'default',
    ) {
        $this->machineId = MachineId::from($machineId);
        $this->selector = self::normalizeSelector($selector);
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }

    public function selector(): string
    {
        return $this->selector;
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
