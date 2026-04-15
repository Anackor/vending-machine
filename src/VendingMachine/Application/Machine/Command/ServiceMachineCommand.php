<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\ValueObject\AvailableChange;
use VendingMachine\Domain\Machine\ValueObject\MachineId;
use VendingMachine\Domain\Machine\ValueObject\Selector;
use VendingMachine\Domain\Machine\ValueObject\StockQuantity;

/**
 * Carries the stock and change configuration used by the service operation.
 */
final readonly class ServiceMachineCommand
{
    private MachineId $machineId;

    /**
     * @var array<string, StockQuantity>
     */
    private array $productQuantities;

    private AvailableChange $availableChange;

    /**
     * @param array<int|string, mixed> $productQuantities
     * @param AvailableChange|array<int|string, mixed> $availableChange
     */
    public function __construct(
        array $productQuantities,
        AvailableChange|array $availableChange,
        MachineId|string $machineId = 'default',
    ) {
        $this->machineId = MachineId::from($machineId);
        $this->productQuantities = self::normalizeProductQuantities($productQuantities);
        $this->availableChange = AvailableChange::from($availableChange);
    }

    /**
     * @return array<int, int>
     */
    public function availableChangeCounts(): array
    {
        return $this->availableChange->counts();
    }

    public function availableChange(): AvailableChange
    {
        return $this->availableChange;
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }

    /**
     * @return array<string, StockQuantity>
     */
    public function productQuantities(): array
    {
        return $this->productQuantities;
    }

    /**
     * @param array<int|string, mixed> $productQuantities
     *
     * @return array<string, StockQuantity>
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

            if (!$quantity instanceof StockQuantity && !is_int($quantity)) {
                throw new InvalidArgumentException('Service product quantities must be integers.');
            }

            $normalized[Selector::fromString($selector)->value()] = StockQuantity::from($quantity);
        }

        ksort($normalized);

        return $normalized;
    }
}
