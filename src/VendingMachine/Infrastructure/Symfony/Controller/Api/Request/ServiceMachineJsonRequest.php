<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for servicing stock and change.
 */
final readonly class ServiceMachineJsonRequest
{
    /**
     * @param array<array-key, mixed> $productQuantities
     * @param array<int, mixed> $availableChangeCounts
     */
    private function __construct(
        private array $productQuantities,
        private array $availableChangeCounts,
    ) {
    }

    public static function fromPayload(JsonPayload $payload, CoinInputNormalizer $coinInputNormalizer): self
    {
        return new self(
            $payload->requiredObject('productQuantities'),
            $coinInputNormalizer->coinCountKeysToCents($payload->requiredObject('availableChangeCounts')),
        );
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
