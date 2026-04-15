<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for servicing stock and change.
 *
 * The service endpoint has the richest input shape, so this passive DTO keeps
 * object validation and denomination-key normalization out of the controller. The
 * application command still owns use-case validation such as selector and
 * quantity normalization. A shared application DTO was considered, but the JSON
 * key format for available change is an adapter concern and should not become
 * part of the application boundary.
 */
final readonly class ServiceMachineJsonRequest
{
    /**
     * @param array<array-key, mixed> $productQuantities
     * @param array<int, mixed> $availableChangeCounts
     */
    public function __construct(
        private array $productQuantities,
        private array $availableChangeCounts,
    ) {
    }

    /**
     * @return array<array-key, mixed>
     */
    public function productQuantities(): array
    {
        return $this->productQuantities;
    }

    /**
     * @return array<int, mixed>
     */
    public function availableChangeCounts(): array
    {
        return $this->availableChangeCounts;
    }
}
