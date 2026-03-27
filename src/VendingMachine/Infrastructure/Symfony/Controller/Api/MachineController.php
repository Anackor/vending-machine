<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use JsonException;
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

#[AsController]
#[Route('/api/machine', name: 'machine_')]
final readonly class MachineController
{
    public function __construct(
        private MachineJsonRequestFactory $requestFactory,
        private MachineJsonResponseFactory $responseFactory,
    ) {
    }

    #[Route('', name: 'state', methods: ['GET'])]
    public function machineState(GetMachineStateHandler $handler): JsonResponse
    {
        try {
            return $this->responseFactory->machineState(
                $handler->handle($this->requestFactory->createGetMachineStateQuery()),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responseFactory->machineOperationFailed($exception);
        }
    }

    #[Route('/insert-coin', name: 'insert_coin', methods: ['POST'])]
    public function insertCoin(Request $request, InsertCoinHandler $handler): JsonResponse
    {
        try {
            $command = $this->requestFactory->createInsertCoinCommand($request);

            return $this->responseFactory->insertCoin(
                $command,
                $handler->handle($command),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responseFactory->machineOperationFailed($exception);
        } catch (InvalidArgumentException | JsonException $exception) {
            return $this->responseFactory->invalidRequest($exception);
        }
    }

    #[Route('/return-coin', name: 'return_coin', methods: ['POST'])]
    public function returnCoin(ReturnInsertedMoneyHandler $handler): JsonResponse
    {
        try {
            return $this->responseFactory->returnInsertedMoney(
                $handler->handle($this->requestFactory->createReturnInsertedMoneyCommand()),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responseFactory->machineOperationFailed($exception);
        }
    }

    #[Route('/select-product', name: 'select_product', methods: ['POST'])]
    public function selectProduct(Request $request, SelectProductHandler $handler): JsonResponse
    {
        try {
            return $this->responseFactory->selectProduct(
                $handler->handle($this->requestFactory->createSelectProductCommand($request)),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responseFactory->machineOperationFailed($exception);
        } catch (InvalidArgumentException | JsonException $exception) {
            return $this->responseFactory->invalidRequest($exception);
        }
    }

    #[Route('/service', name: 'service', methods: ['POST'])]
    public function service(Request $request, ServiceMachineHandler $handler): JsonResponse
    {
        try {
            return $this->responseFactory->serviceMachine(
                $handler->handle($this->requestFactory->createServiceMachineCommand($request)),
            );
        } catch (MachineOperationFailed $exception) {
            return $this->responseFactory->machineOperationFailed($exception);
        } catch (InvalidArgumentException | JsonException $exception) {
            return $this->responseFactory->invalidRequest($exception);
        }
    }
}
