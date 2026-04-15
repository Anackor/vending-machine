<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\MachineId;

/**
 * Carries the stock and change configuration used by the service operation.
 */
final readonly class ServiceMachineCommand
{
    private MachineId $machineId;

    /**
     * @var array<string, int>
     */
    private array $productQuantities;

    /**
     * @var array<int, int>
     */
    private array $availableChangeCounts;

    /**
     * @param array<int|string, mixed> $productQuantities
     * @param array<int|string, mixed> $availableChangeCounts
     */
    public function __construct(
        array $productQuantities,
        array $availableChangeCounts,
        MachineId|string $machineId = 'default',
    ) {
        $this->machineId = MachineId::from($machineId);
        $this->productQuantities = self::normalizeProductQuantities($productQuantities);
        $this->availableChangeCounts = self::normalizeCoinCounts($availableChangeCounts);
    }

    /**
     * @return array<int, int>
     */
    public function availableChangeCounts(): array
    {
        return $this->availableChangeCounts;
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }

    /**
     * @return array<string, int>
     */
    public function productQuantities(): array
    {
        return $this->productQuantities;
    }

    /**
     * @param array<int|string, mixed> $counts
     *
     * @return array<int, int>
     */
    private static function normalizeCoinCounts(array $counts): array
    {
        $normalized = [];

        foreach ($counts as $denomination => $count) {
            if (!is_int($count)) {
                throw new InvalidArgumentException('Available change counts must be integers.');
            }

            if ($count < 0) {
                throw new InvalidArgumentException('Available change counts cannot be negative.');
            }

            if (is_string($denomination) && !ctype_digit($denomination)) {
                throw new InvalidArgumentException('Available change denomination keys must be integer values.');
            }

            $normalized[(int) $denomination] = $count;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param array<int|string, mixed> $productQuantities
     *
     * @return array<string, int>
     */
    private static function normalizeProductQuantities(array $productQuantities): array
    {
        if ($productQuantities === []) {
            throw new InvalidArgumentException('Service product quantities cannot be empty.');
        }

        $normalized = [];

        foreach ($productQuantities as $selector => $quantity) {
            if (is_int($selector)) {
                throw new InvalidArgumentException('Service product selectors must be strings.');
            }

            $normalizedSelector = strtolower(trim($selector));

            if ($normalizedSelector === '') {
                throw new InvalidArgumentException('Service product selectors cannot be empty.');
            }

            if (!is_int($quantity)) {
                throw new InvalidArgumentException('Service product quantities must be integers.');
            }

            if ($quantity < 0) {
                throw new InvalidArgumentException('Service product quantities cannot be negative.');
            }

            $normalized[$normalizedSelector] = $quantity;
        }

        ksort($normalized);

        return $normalized;
    }
}
