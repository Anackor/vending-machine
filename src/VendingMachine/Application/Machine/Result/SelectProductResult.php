<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\ValueObject\ProductName;
use VendingMachine\Domain\Machine\ValueObject\Selector;

/**
 * Wraps the dispensed product, change, and resulting machine snapshot of a purchase.
 */
final readonly class SelectProductResult
{
    /**
     * @var array<int, int>
     */
    private array $dispensedChangeCounts;

    private ProductName $dispensedProductName;
    private Selector $dispensedProductSelector;

    /**
     * @param array<int|string, mixed> $dispensedChangeCounts
     */
    public function __construct(
        Selector|string $dispensedProductSelector,
        ProductName|string $dispensedProductName,
        array $dispensedChangeCounts,
        private MachineSnapshot $machineSnapshot,
    ) {
        $this->dispensedProductSelector = Selector::from($dispensedProductSelector);
        $this->dispensedProductName = ProductName::from($dispensedProductName);

        $this->dispensedChangeCounts = self::normalizeCoinCounts($dispensedChangeCounts);
    }

    /**
     * @return array<int, int>
     */
    public function dispensedChangeCounts(): array
    {
        return $this->dispensedChangeCounts;
    }

    public function dispensedProductName(): string
    {
        return $this->dispensedProductName->value();
    }

    public function dispensedProductSelector(): Selector
    {
        return $this->dispensedProductSelector;
    }

    public function machineSnapshot(): MachineSnapshot
    {
        return $this->machineSnapshot;
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
                throw new InvalidArgumentException('Dispensed change counts must be integers.');
            }

            if ($count < 0) {
                throw new InvalidArgumentException('Dispensed change counts cannot be negative.');
            }

            if (is_string($denomination) && !ctype_digit($denomination)) {
                throw new InvalidArgumentException('Dispensed change denomination keys must be integer values.');
            }

            $normalized[(int) $denomination] = $count;
        }

        ksort($normalized);

        return $normalized;
    }
}
