<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Handler;

use InvalidArgumentException;
use VendingMachine\Application\Machine\Command\ServiceMachineCommand;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Application\Machine\Result\ServiceMachineResult;
use VendingMachine\Domain\Machine\Exception\InvalidServiceConfiguration;
use VendingMachine\Domain\Machine\Exception\PendingBalanceDuringService;

final readonly class ServiceMachineHandler
{
    public function __construct(
        private MachineRepository $machineRepository,
        private MachineSnapshotFactory $machineSnapshotFactory,
        private MachineFailureFactory $machineFailureFactory,
    ) {
    }

    public function handle(ServiceMachineCommand $command): ServiceMachineResult
    {
        $machine = $this->machineRepository->find($command->machineId());

        if ($machine === null) {
            throw $this->machineFailureFactory->machineNotFound($command->machineId());
        }

        try {
            $updatedMachine = $machine->service(
                $command->productQuantities(),
                $command->availableChangeCounts(),
            );
        } catch (InvalidArgumentException $exception) {
            throw $this->machineFailureFactory->invalidServiceConfiguration(
                $command->machineId(),
                $exception,
            );
        } catch (InvalidServiceConfiguration | PendingBalanceDuringService $exception) {
            throw $this->machineFailureFactory->fromDomainThrowable(
                $command->machineId(),
                $exception,
            );
        }

        $this->machineRepository->save($command->machineId(), $updatedMachine);

        return new ServiceMachineResult(
            $this->machineSnapshotFactory->create($command->machineId(), $updatedMachine),
        );
    }
}
