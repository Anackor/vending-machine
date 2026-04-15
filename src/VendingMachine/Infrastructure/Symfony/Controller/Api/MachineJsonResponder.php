<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Result\GetMachineStateResult;
use VendingMachine\Application\Machine\Result\InsertCoinResult;
use VendingMachine\Application\Machine\Result\ReturnInsertedMoneyResult;
use VendingMachine\Application\Machine\Result\SelectProductResult;
use VendingMachine\Application\Machine\Result\ServiceMachineResult;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\CoinJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\MachineFailureJsonPresenter;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter\MachineSnapshotJsonPresenter;

/**
 * Converts presented API payloads into Symfony JSON responses.
 */
final readonly class MachineJsonResponder
{
    public function __construct(
        private CoinJsonPresenter $coinPresenter,
        private MachineSnapshotJsonPresenter $snapshotPresenter,
        private MachineFailureJsonPresenter $failurePresenter,
    ) {
    }

    public function invalidRequest(InvalidArgumentException|JsonException $exception): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => [
                    'code' => 'invalid_request',
                    'message' => $exception->getMessage(),
                    'context' => [],
                ],
            ],
            Response::HTTP_BAD_REQUEST,
        );
    }

    public function machineOperationFailed(MachineOperationFailed $exception): JsonResponse
    {
        $failure = $exception->failure();

        return new JsonResponse(
            $this->failurePresenter->present($failure),
            $this->statusCodeFor($failure->code()),
        );
    }

    public function machineState(GetMachineStateResult $result): JsonResponse
    {
        return new JsonResponse(
            ['machine' => $this->snapshotPresenter->present($result->machineSnapshot())],
        );
    }

    public function insertCoin(InsertCoinCommand $command, InsertCoinResult $result): JsonResponse
    {
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'coin_inserted',
                    'coins' => $this->coinPresenter->coins($command->coinCents()),
                ],
                'machine' => $this->snapshotPresenter->present($result->machineSnapshot()),
            ],
        );
    }

    public function returnInsertedMoney(ReturnInsertedMoneyResult $result): JsonResponse
    {
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'money_returned',
                    'returnedCoinCounts' => $this->coinPresenter->coinCounts($result->returnedCoinCounts()),
                ],
                'machine' => $this->snapshotPresenter->present($result->machineSnapshot()),
            ],
        );
    }

    public function serviceMachine(ServiceMachineResult $result): JsonResponse
    {
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'machine_serviced',
                ],
                'machine' => $this->snapshotPresenter->present($result->machineSnapshot()),
            ],
        );
    }

    public function selectProduct(SelectProductResult $result): JsonResponse
    {
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'product_selected',
                    'dispensedProduct' => [
                        'name' => $result->dispensedProductName(),
                        'selector' => $result->dispensedProductSelector()->value(),
                    ],
                    'dispensedChangeCounts' => $this->coinPresenter->coinCounts($result->dispensedChangeCounts()),
                ],
                'machine' => $this->snapshotPresenter->present($result->machineSnapshot()),
            ],
        );
    }

    private function statusCodeFor(MachineFailureCode $code): int
    {
        return match ($code) {
            MachineFailureCode::MachineNotFound,
            MachineFailureCode::ProductNotFound => Response::HTTP_NOT_FOUND,
            MachineFailureCode::UnsupportedCoin,
            MachineFailureCode::InvalidServiceConfiguration => Response::HTTP_BAD_REQUEST,
            MachineFailureCode::ExactChangeUnavailable,
            MachineFailureCode::InsufficientBalance,
            MachineFailureCode::PendingBalanceDuringService,
            MachineFailureCode::ProductOutOfStock => Response::HTTP_CONFLICT,
        };
    }
}
