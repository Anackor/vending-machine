<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

final readonly class InsertCoinResult
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
