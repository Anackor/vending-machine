<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Double;

use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\MachineId;

final class InMemoryMachineRepository implements MachineRepository
{
    /**
     * @param array<string, Machine> $machines
     */
    public function __construct(
        private array $machines = [],
    ) {
    }

    public function find(MachineId $machineId): ?Machine
    {
        return $this->machines[$machineId->value()] ?? null;
    }

    public function save(MachineId $machineId, Machine $machine): void
    {
        ++$this->saveCount;
        $this->lastSavedMachineId = $machineId->value();
        $this->machines[$machineId->value()] = $machine;
    }

    public function machine(MachineId|string $machineId): ?Machine
    {
        $key = $machineId instanceof MachineId ? $machineId->value() : $machineId;

        return $this->machines[$key] ?? null;
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
