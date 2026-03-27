<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Handler;

use InvalidArgumentException;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Application\Machine\Result\InsertCoinResult;

final readonly class InsertCoinHandler
{
    public function __construct(
        private MachineRepository $machineRepository,
        private MachineSnapshotFactory $machineSnapshotFactory,
        private MachineFailureFactory $machineFailureFactory,
    ) {
    }

    public function handle(InsertCoinCommand $command): InsertCoinResult
    {
        $machine = $this->machineRepository->find($command->machineId());

        if ($machine === null) {
            throw $this->machineFailureFactory->machineNotFound($command->machineId());
        }

        try {
            $updatedMachine = $machine->insertCoinValue($command->coinCents());
        } catch (InvalidArgumentException $exception) {
            throw $this->machineFailureFactory->unsupportedCoin(
                $command->machineId(),
                $command->coinCents(),
                $exception,
            );
        }

        $this->machineRepository->save($command->machineId(), $updatedMachine);

        return new InsertCoinResult(
            $this->machineSnapshotFactory->create($command->machineId(), $updatedMachine),
        );
    }
}
