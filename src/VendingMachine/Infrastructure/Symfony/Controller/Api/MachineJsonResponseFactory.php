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
use VendingMachine\Application\Machine\Result\MachineSnapshot;
use VendingMachine\Application\Machine\Result\ProductSnapshot;
use VendingMachine\Application\Machine\Result\ReturnInsertedMoneyResult;
use VendingMachine\Application\Machine\Result\SelectProductResult;
use VendingMachine\Application\Machine\Result\ServiceMachineResult;

final class MachineJsonResponseFactory
{
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
            [
                'error' => [
                    'code' => $failure->code()->value,
                    'message' => $failure->message(),
                    'context' => $failure->context(),
                ],
            ],
            $this->statusCodeFor($failure->code()),
        );
    }

    public function machineState(GetMachineStateResult $result): JsonResponse
    {
        return new JsonResponse(
            ['machine' => $this->machine($result->machineSnapshot())],
        );
    }

    public function insertCoin(InsertCoinCommand $command, InsertCoinResult $result): JsonResponse
    {
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'coin_inserted',
                    'coinCents' => $command->coinCents(),
                ],
                'machine' => $this->machine($result->machineSnapshot()),
            ],
        );
    }

    public function returnInsertedMoney(ReturnInsertedMoneyResult $result): JsonResponse
    {
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'money_returned',
                    'returnedCoinCounts' => $result->returnedCoinCounts(),
                ],
                'machine' => $this->machine($result->machineSnapshot()),
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
                'machine' => $this->machine($result->machineSnapshot()),
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
                        'selector' => $result->dispensedProductSelector(),
                    ],
                    'dispensedChangeCounts' => $result->dispensedChangeCounts(),
                ],
                'machine' => $this->machine($result->machineSnapshot()),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function machine(MachineSnapshot $snapshot): array
    {
        return [
            'machineId' => $snapshot->machineId(),
            'insertedBalanceCents' => $snapshot->insertedBalanceCents(),
            'hasPendingBalance' => $snapshot->hasPendingBalance(),
            'insertedCoins' => $snapshot->insertedCoins(),
            'availableChangeCounts' => $snapshot->availableChangeCounts(),
            'products' => array_map(
                static fn (ProductSnapshot $product): array => [
                    'selector' => $product->selector(),
                    'name' => $product->name(),
                    'priceCents' => $product->priceCents(),
                    'quantity' => $product->quantity(),
                    'available' => $product->isAvailable(),
                ],
                $snapshot->products(),
            ),
        ];
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
