<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Double;

use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\Machine;

final class InMemoryMachineRepository implements MachineRepository
{
    /**
     * @param array<string, Machine> $machines
     */
    public function __construct(
        private array $machines = [],
    ) {
    }

    public function find(string $machineId): ?Machine
    {
        return $this->machines[$machineId] ?? null;
    }

    public function save(string $machineId, Machine $machine): void
    {
        ++$this->saveCount;
        $this->lastSavedMachineId = $machineId;
        $this->machines[$machineId] = $machine;
    }

    public function machine(string $machineId): ?Machine
    {
        return $this->machines[$machineId] ?? null;
    }

    public function lastSavedMachineId(): ?string
    {
        return $this->lastSavedMachineId;
    }

    public function saveCount(): int
    {
        return $this->saveCount;
    }

    private ?string $lastSavedMachineId = null;
    private int $saveCount = 0;
}
