<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Application\Machine\Result\GetMachineStateResult;

/**
 * Coordinates the read-only use case that returns the current machine snapshot.
 */
final readonly class GetMachineStateHandler
{
    public function __construct(
        private MachineRepository $machineRepository,
        private MachineSnapshotFactory $machineSnapshotFactory,
        private MachineFailureFactory $machineFailureFactory,
    ) {
    }

    public function handle(GetMachineStateQuery $query): GetMachineStateResult
    {
        $machine = $this->machineRepository->find($query->machineId());

        if ($machine === null) {
            throw $this->machineFailureFactory->machineNotFound($query->machineId());
        }

        return new GetMachineStateResult(
            $this->machineSnapshotFactory->create($query->machineId(), $machine),
        );
    }
}
