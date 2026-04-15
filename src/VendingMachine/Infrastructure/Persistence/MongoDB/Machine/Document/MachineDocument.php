<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\ValueObject\AvailableChange;
use VendingMachine\Domain\Machine\ValueObject\InsertedCoins;

/**
 * Persistence DTO that stores one complete machine aggregate as a single MongoDB document.
 */
final readonly class MachineDocument
{
    private string $machineId;

    /**
     * @var list<ProductStockDocument>
     */
    private array $productStocks;

    private AvailableChange $availableChange;
    private InsertedCoins $insertedCoins;

    /**
     * @param list<ProductStockDocument> $productStocks
     * @param AvailableChange|array<int|string, mixed> $availableChange
     * @param InsertedCoins|array<int|string, mixed> $insertedCoins
     */
    public function __construct(
        string $machineId,
        array $productStocks,
        AvailableChange|array $availableChange,
        InsertedCoins|array $insertedCoins,
    ) {
        $machineId = trim($machineId);

        if ($machineId === '') {
            throw new InvalidArgumentException('Persisted machine id cannot be empty.');
        }

        if ($productStocks === []) {
            throw new InvalidArgumentException('Persisted machine document must contain product stocks.');
        }

        $selectors = [];

        foreach ($productStocks as $productStock) {
            $selector = $productStock->selector()->value();

            if (isset($selectors[$selector])) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate persisted product selector "%s" detected.',
                    $selector,
                ));
            }

            $selectors[$selector] = true;
        }

        $this->machineId = $machineId;
        $this->productStocks = $productStocks;
        $this->availableChange = AvailableChange::from($availableChange);
        $this->insertedCoins = InsertedCoins::from($insertedCoins);
    }

    public function machineId(): string
    {
        return $this->machineId;
    }

    /**
     * @return list<ProductStockDocument>
     */
    public function productStocks(): array
    {
        return $this->productStocks;
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

    /**
     * @return array<int, int>
     */
    public function insertedCoinCounts(): array
    {
        return $this->insertedCoins->counts();
    }

    public function insertedCoins(): InsertedCoins
    {
        return $this->insertedCoins;
    }
}
