<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use VendingMachine\Domain\Machine\MachineId;
use VendingMachine\Domain\Machine\Selector;

/**
 * Carries the input required to select a product from the machine.
 */
final readonly class SelectProductCommand
{
    private MachineId $machineId;
    private Selector $selector;

    public function __construct(
        Selector|string $selector,
        MachineId|string $machineId = 'default',
    ) {
        $this->machineId = MachineId::from($machineId);
        $this->selector = Selector::from($selector);
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }

    public function selector(): Selector
    {
        return $this->selector;
    }
}
