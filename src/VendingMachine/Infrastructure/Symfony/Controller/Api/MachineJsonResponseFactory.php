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

/**
 * Serializes application results and failures into the stable JSON API contract.
 */
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
                    'message' => $this->failureMessage($failure->code(), $failure->message()),
                    'context' => $this->failureContext($failure->context()),
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
        // The response stays reviewer-friendly while the application still works with integer cents.
        return new JsonResponse(
            [
                'event' => [
                    'type' => 'coin_inserted',
                    'coins' => $this->coins($command->coinCents()),
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
                    'returnedCoinCounts' => $this->coinCounts($result->returnedCoinCounts()),
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
                    'dispensedChangeCounts' => $this->coinCounts($result->dispensedChangeCounts()),
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
            'insertedBalanceCoins' => $this->coins($snapshot->insertedBalanceCents()),
            'hasPendingBalance' => $snapshot->hasPendingBalance(),
            'insertedCoins' => $this->coinCounts($snapshot->insertedCoins()),
            'availableChangeCounts' => $this->coinCounts($snapshot->availableChangeCounts()),
            'products' => array_map(
                fn (ProductSnapshot $product): array => [
                    'selector' => $product->selector(),
                    'name' => $product->name(),
                    'priceCoins' => $this->coins($product->priceCents()),
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

    private function coins(int $coinCents): int|float
    {
        // Render clean decimal values for the HTTP boundary without reintroducing floats into the core.
        if ($coinCents % 100 === 0) {
            return intdiv($coinCents, 100);
        }

        return round($coinCents / 100, 2);
    }

    /**
     * @param array<int|string, int> $coinCounts
     *
     * @return array<string, int>
     */
    private function coinCounts(array $coinCounts): array
    {
        $normalized = [];
        ksort($coinCounts);

        foreach ($coinCounts as $coinCents => $quantity) {
            $normalized[$this->coinKey((int) $coinCents)] = $quantity;
        }

        return $normalized;
    }

    /**
     * @param array<string, bool|float|int|string> $context
     *
     * @return array<string, bool|float|int|string>
     */
    private function failureContext(array $context): array
    {
        $normalized = [];

        foreach ($context as $key => $value) {
            if ($key === 'coinCents' && is_int($value)) {
                $normalized['coins'] = $this->coins($value);
                continue;
            }

            if (str_ends_with($key, 'Cents') && is_int($value)) {
                $normalized[sprintf('%sCoins', substr($key, 0, -5))] = $this->coins($value);
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function failureMessage(MachineFailureCode $code, string $message): string
    {
        return match ($code) {
            MachineFailureCode::ExactChangeUnavailable,
            MachineFailureCode::InsufficientBalance,
            MachineFailureCode::InvalidServiceConfiguration,
            MachineFailureCode::UnsupportedCoin => preg_replace_callback(
                '/"(\d+)"/',
                fn (array $matches): string => sprintf('"%s"', $this->coinKey((int) $matches[1])),
                $message,
            ) ?? $message,
            default => $message,
        };
    }

    private function coinKey(int $coinCents): string
    {
        $coins = $this->coins($coinCents);

        if (is_int($coins)) {
            return (string) $coins;
        }

        return number_format($coins, 2, '.', '');
    }
}
