<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Mapper;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\InsertedCoins;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\Money;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\Selector;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document\MachineDocument;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document\ProductStockDocument;

/**
 * Maps the domain aggregate to the MongoDB document shape and back again.
 */
final class MachineDocumentMapper
{
    public function fromDomain(string $machineId, Machine $machine): MachineDocument
    {
        // Keep persistence DTOs free of domain behavior while preserving the aggregate shape.
        $productStocks = [];

        foreach ($machine->productStocks() as $productStock) {
            $product = $productStock->product();

            $productStocks[] = new ProductStockDocument(
                $product->selector()->value(),
                $product->price()->cents(),
                $productStock->quantity(),
                $product->name(),
            );
        }

        return new MachineDocument(
            $machineId,
            $productStocks,
            $machine->availableChange()->counts(),
            $machine->insertedCoins()->counts(),
        );
    }

    public function toDomain(MachineDocument $document): Machine
    {
        // Rebuild the aggregate from persistence primitives without leaking Mongo-specific types.
        $productStocks = [];

        foreach ($document->productStocks() as $productStock) {
            $productStocks[] = new ProductStock(
                new Product(
                    Selector::fromString($productStock->selector()),
                    Money::fromCents($productStock->priceCents()),
                    $productStock->name(),
                ),
                $productStock->quantity(),
            );
        }

        return Machine::initialize(
            $productStocks,
            AvailableChange::fromCounts($document->availableChangeCounts()),
            InsertedCoins::fromCounts($document->insertedCoinCounts()),
        );
    }

    /**
     * @param array<string, mixed> $document
     */
    public function fromPersistence(array $document): MachineDocument
    {
        return new MachineDocument(
            $this->extractMachineId($document),
            $this->extractProductStocks($document),
            $this->extractCoinCounts($document, 'availableChange'),
            $this->extractCoinCounts($document, 'insertedCoins'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(MachineDocument $document): array
    {
        $productStocks = [];

        foreach ($document->productStocks() as $productStock) {
            $productStocks[] = [
                'selector' => $productStock->selector(),
                'name' => $productStock->name(),
                'priceCents' => $productStock->priceCents(),
                'quantity' => $productStock->quantity(),
            ];
        }

        return [
            '_id' => $document->machineId(),
            'products' => $productStocks,
            'availableChange' => $this->stringifyCoinCounts($document->availableChangeCounts()),
            'insertedCoins' => $this->stringifyCoinCounts($document->insertedCoinCounts()),
        ];
    }

    /**
     * @param array<string, mixed> $document
     *
     * @return array<int|string, mixed>
     */
    private function extractCoinCounts(array $document, string $field): array
    {
        if (!array_key_exists($field, $document) || !is_array($document[$field])) {
            throw new InvalidArgumentException(sprintf(
                'Persisted machine document field "%s" must be an array.',
                $field,
            ));
        }

        return $document[$field];
    }

    /**
     * @param array<string, mixed> $document
     *
     * @return list<ProductStockDocument>
     */
    private function extractProductStocks(array $document): array
    {
        // Products are stored as a list so selector order remains explicit and duplicate-safe.
        if (!array_key_exists('products', $document) || !is_array($document['products'])) {
            throw new InvalidArgumentException('Persisted machine document field "products" must be a list.');
        }

        if (!array_is_list($document['products'])) {
            throw new InvalidArgumentException('Persisted machine document products must be a list.');
        }

        $productStocks = [];

        foreach ($document['products'] as $productStock) {
            if (!is_array($productStock)) {
                throw new InvalidArgumentException('Persisted machine products must be document arrays.');
            }

            /** @var array<string, mixed> $productStock */
            $productStocks[] = new ProductStockDocument(
                $this->extractRequiredString($productStock, 'selector', 'Persisted product selector'),
                $this->extractRequiredInt($productStock, 'priceCents', 'Persisted product price cents'),
                $this->extractRequiredInt($productStock, 'quantity', 'Persisted product quantity'),
                $this->extractRequiredString($productStock, 'name', 'Persisted product name'),
            );
        }

        return $productStocks;
    }

    /**
     * @param array<string, mixed> $document
     */
    private function extractMachineId(array $document): string
    {
        if (!array_key_exists('_id', $document) || !is_string($document['_id'])) {
            throw new InvalidArgumentException('Persisted machine document id must be a string.');
        }

        return $document['_id'];
    }

    /**
     * @param array<string, mixed> $document
     */
    private function extractRequiredInt(array $document, string $field, string $label): int
    {
        if (!array_key_exists($field, $document) || !is_int($document[$field])) {
            throw new InvalidArgumentException(sprintf('%s must be an integer.', $label));
        }

        return $document[$field];
    }

    /**
     * @param array<string, mixed> $document
     */
    private function extractRequiredString(array $document, string $field, string $label): string
    {
        if (!array_key_exists($field, $document) || !is_string($document[$field])) {
            throw new InvalidArgumentException(sprintf('%s must be a string.', $label));
        }

        return $document[$field];
    }

    /**
     * @param array<int, int> $counts
     *
     * @return array<int, int>
     */
    private function stringifyCoinCounts(array $counts): array
    {
        $normalized = [];

        foreach ($counts as $denomination => $count) {
            $normalized[$denomination] = $count;
        }

        return $normalized;
    }
}
