<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Handler\GetMachineStateHandler;
use VendingMachine\Application\Machine\Handler\InsertCoinHandler;
use VendingMachine\Application\Machine\Handler\ReturnInsertedMoneyHandler;
use VendingMachine\Application\Machine\Handler\SelectProductHandler;
use VendingMachine\Application\Machine\Handler\ServiceMachineHandler;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Exception\InvalidMachineJsonRequest;

/**
 * Thin Symfony controller that exposes the reviewer-facing HTTP API.
 */
#[AsController]
#[Route('/api/machine', name: 'machine_')]
final readonly class MachineController
{
    public function __construct(
        private MachineJsonRequestMapper $requestMapper,
        private MachineJsonResponder $responder,
    ) {
    }

    #[Route('', name: 'state', methods: ['GET'])]
    public function machineState(GetMachineStateHandler $handler): JsonResponse
    {
        try {
            return $this->responder->machineState(
                $handler->handle($this->requestMapper->createGetMachineStateQuery()),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responder->machineOperationFailed($exception);
        }
    }

    #[Route('/insert-coin', name: 'insert_coin', methods: ['POST'])]
    public function insertCoin(Request $request, InsertCoinHandler $handler): JsonResponse
    {
        try {
            $command = $this->requestMapper->createInsertCoinCommand($request);

            return $this->responder->insertCoin(
                $command,
                $handler->handle($command),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responder->machineOperationFailed($exception);
        } catch (InvalidMachineJsonRequest $exception) {
            return $this->responder->invalidRequest($exception);
        }
    }

    #[Route('/return-coin', name: 'return_coin', methods: ['POST'])]
    public function returnCoin(ReturnInsertedMoneyHandler $handler): JsonResponse
    {
        try {
            return $this->responder->returnInsertedMoney(
                $handler->handle($this->requestMapper->createReturnInsertedMoneyCommand()),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responder->machineOperationFailed($exception);
        }
    }

    #[Route('/select-product', name: 'select_product', methods: ['POST'])]
    public function selectProduct(Request $request, SelectProductHandler $handler): JsonResponse
    {
        try {
            return $this->responder->selectProduct(
                $handler->handle($this->requestMapper->createSelectProductCommand($request)),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responder->machineOperationFailed($exception);
        } catch (InvalidMachineJsonRequest $exception) {
            return $this->responder->invalidRequest($exception);
        }
    }

    #[Route('/service', name: 'service', methods: ['POST'])]
    public function service(Request $request, ServiceMachineHandler $handler): JsonResponse
    {
        try {
            return $this->responder->serviceMachine(
                $handler->handle($this->requestMapper->createServiceMachineCommand($request)),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responder->machineOperationFailed($exception);
        } catch (InvalidMachineJsonRequest $exception) {
            return $this->responder->invalidRequest($exception);
        }
    }
}
