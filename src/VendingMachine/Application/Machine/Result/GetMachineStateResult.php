<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

/**
 * Wraps the read-only machine snapshot returned by the query handler.
 */
final readonly class GetMachineStateResult
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
