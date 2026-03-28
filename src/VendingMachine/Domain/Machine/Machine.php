<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\Exception\ExactChangeNotAvailable;
use VendingMachine\Domain\Machine\Exception\InsufficientBalance;
use VendingMachine\Domain\Machine\Exception\InvalidServiceConfiguration;
use VendingMachine\Domain\Machine\Exception\PendingBalanceDuringService;
use VendingMachine\Domain\Machine\Exception\ProductNotFound;
use VendingMachine\Domain\Machine\Exception\ProductOutOfStock;

/**
 * Aggregate root that keeps stock, inserted balance, and available change consistent.
 */
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

    public function insertCoin(Coin $coin): self
    {
        return new self(
            array_values($this->productStocks),
            $this->availableChange,
            $this->insertedCoins->addCoin($coin),
        );
    }

    public function insertCoinValue(int $cents): self
    {
        return $this->insertCoin(Coin::fromCents($cents));
    }

    public function canPurchase(Selector $selector): bool
    {
        try {
            $this->preparePurchase($selector);
        } catch (
            ProductNotFound |
            ProductOutOfStock |
            InsufficientBalance |
            ExactChangeNotAvailable
        ) {
            return false;
        }

        return true;
    }

    public function purchase(Selector $selector): PurchaseResult
    {
        // A successful purchase commits inserted coins, dispenses the product, and returns exact change.
        [$productStock, $committedAvailableChange, $dispensedChange] = $this->preparePurchase($selector);

        $updatedProductStocks = $this->productStocks;
        $updatedProductStocks[$selector->value()] = $productStock->decrement();

        $updatedMachine = new self(
            array_values($updatedProductStocks),
            $committedAvailableChange->remove($dispensedChange),
            $this->insertedCoins->clear(),
        );

        return new PurchaseResult(
            $updatedMachine,
            $productStock->product(),
            $dispensedChange,
        );
    }

    public function refund(): RefundResult
    {
        // Refund only clears the customer session; it does not mutate the machine-owned change pool.
        return new RefundResult(
            new self(
                array_values($this->productStocks),
                $this->availableChange,
                $this->insertedCoins->clear(),
            ),
            $this->insertedCoins,
        );
    }

    /**
     * @param array<string, mixed> $stockCounts
     * @param array<int|string, mixed> $availableChangeCounts
     */
    public function service(array $stockCounts, array $availableChangeCounts): self
    {
        // Service is intentionally blocked while a customer still has pending balance.
        if ($this->hasPendingBalance()) {
            throw new PendingBalanceDuringService('Machine service requires no pending customer balance.');
        }

        $updatedProductStocks = [];

        foreach ($this->productStocks as $selector => $productStock) {
            if (!array_key_exists($selector, $stockCounts)) {
                throw new InvalidServiceConfiguration(sprintf('Missing stock count for selector "%s".', $selector));
            }

            $quantity = $stockCounts[$selector];

            if (!is_int($quantity)) {
                throw new InvalidServiceConfiguration(sprintf('Stock count for selector "%s" must be an integer.', $selector));
            }

            $updatedProductStocks[$selector] = $productStock->withQuantity($quantity);
        }

        foreach (array_keys($stockCounts) as $selector) {
            if (!isset($this->productStocks[$selector])) {
                throw new InvalidServiceConfiguration(sprintf('Unknown selector "%s" in service stock configuration.', $selector));
            }
        }

        return new self(
            array_values($updatedProductStocks),
            AvailableChange::fromCounts($availableChangeCounts),
            $this->insertedCoins,
        );
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

    /**
     * @return array{ProductStock, AvailableChange, CoinInventory}
     */
    private function preparePurchase(Selector $selector): array
    {
        // All purchase invariants are checked here before the aggregate state is actually committed.
        $productStock = $this->productStockFor($selector);

        if ($productStock === null) {
            throw new ProductNotFound(sprintf('Unknown selector "%s".', $selector->value()));
        }

        if (!$productStock->isAvailable()) {
            throw new ProductOutOfStock(sprintf('Product "%s" is out of stock.', $productStock->product()->name()));
        }

        if ($this->insertedBalance()->cents() < $productStock->price()->cents()) {
            throw new InsufficientBalance(sprintf(
                'Inserted balance "%d" is insufficient for product price "%d".',
                $this->insertedBalance()->cents(),
                $productStock->price()->cents(),
            ));
        }

        $committedAvailableChange = $this->availableChange->addInsertedCoins($this->insertedCoins);
        $changeAmount = $this->insertedBalance()->subtract($productStock->price());
        $dispensedChange = $committedAvailableChange->allocateChange($changeAmount);

        if ($dispensedChange === null) {
            throw new ExactChangeNotAvailable(sprintf(
                'Exact change "%d" cannot be returned for selector "%s".',
                $changeAmount->cents(),
                $selector->value(),
            ));
        }

        return [$productStock, $committedAvailableChange, $dispensedChange];
    }
}
