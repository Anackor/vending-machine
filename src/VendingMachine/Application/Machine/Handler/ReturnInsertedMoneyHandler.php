<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Command\ReturnInsertedMoneyCommand;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Application\Machine\Result\ReturnInsertedMoneyResult;

/**
 * Coordinates the refund use case and persists the cleared customer session.
 */
final readonly class ReturnInsertedMoneyHandler
{
    public function __construct(
        private MachineRepository $machineRepository,
        private MachineSnapshotFactory $machineSnapshotFactory,
        private MachineFailureFactory $machineFailureFactory,
    ) {
    }

    public function handle(ReturnInsertedMoneyCommand $command): ReturnInsertedMoneyResult
    {
        $machine = $this->machineRepository->find($command->machineId());

        if ($machine === null) {
            throw $this->machineFailureFactory->machineNotFound($command->machineId());
        }

        $refund = $machine->refund();
        $updatedMachine = $refund->machine();
        $this->machineRepository->save($command->machineId(), $updatedMachine);

        return new ReturnInsertedMoneyResult(
            $refund->returnedCoins()->counts(),
            $this->machineSnapshotFactory->create($command->machineId(), $updatedMachine),
        );
    }
}
