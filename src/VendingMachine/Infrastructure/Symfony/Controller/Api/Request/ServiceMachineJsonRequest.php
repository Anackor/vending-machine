<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for servicing stock and change.
 *
 * Tip: JSON object shape is an adapter concern; service invariants stay in the
 * use case and domain. See docs/architecture/http-api-boundary.md for details.
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
