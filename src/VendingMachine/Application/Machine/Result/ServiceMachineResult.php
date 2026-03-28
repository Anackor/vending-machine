<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

/**
 * Wraps the machine snapshot returned after a successful service operation.
 */
final readonly class ServiceMachineResult
{
    public function __construct(
        private MachineSnapshot $machineSnapshot,
    ) {
    }

    public function machineSnapshot(): MachineSnapshot
    {
        return $this->machineSnapshot;
    }
}
