<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

final readonly class Machine
{
    /**
     * @var array<string, ProductStock>
     */
    private array $productStocks;

    /**
     * @param list<ProductStock> $productStocks
     */
    public function __construct(
        array $productStocks,
        private AvailableChange $availableChange,
        private InsertedCoins $insertedCoins,
    ) {
        if ($productStocks === []) {
            throw new InvalidArgumentException('Machine must define at least one product stock.');
        }

        $this->productStocks = self::indexProductStocks($productStocks);
    }

    /**
     * @param list<ProductStock> $productStocks
     */
    public static function initialize(
        array $productStocks,
        ?AvailableChange $availableChange = null,
        ?InsertedCoins $insertedCoins = null,
    ): self {
        return new self(
            $productStocks,
            $availableChange ?? AvailableChange::empty(),
            $insertedCoins ?? InsertedCoins::empty(),
        );
    }

    /**
     * @return array<string, ProductStock>
     */
    public function productStocks(): array
    {
        return $this->productStocks;
    }

    public function productStockFor(Selector $selector): ?ProductStock
    {
        return $this->productStocks[$selector->value()] ?? null;
    }

    public function availableChange(): AvailableChange
    {
        return $this->availableChange;
    }

    public function insertedCoins(): InsertedCoins
    {
        return $this->insertedCoins;
    }

    public function insertedBalance(): Money
    {
        return $this->insertedCoins->total();
    }

    public function hasPendingBalance(): bool
    {
        return !$this->insertedBalance()->isZero();
    }

    /**
     * @param list<ProductStock> $productStocks
     *
     * @return array<string, ProductStock>
     */
    private static function indexProductStocks(array $productStocks): array
    {
        $indexedStocks = [];

        foreach ($productStocks as $productStock) {
            $selector = $productStock->selector()->value();

            if (isset($indexedStocks[$selector])) {
                throw new InvalidArgumentException(sprintf('Duplicate product selector "%s" detected.', $selector));
            }

            $indexedStocks[$selector] = $productStock;
        }

        return $indexedStocks;
    }
}
