<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter;

use VendingMachine\Application\Machine\Result\MachineSnapshot;
use VendingMachine\Application\Machine\Result\ProductSnapshot;

/**
 * Presents an application machine snapshot as the stable HTTP JSON shape.
 *
 * The application snapshot is a read model for use cases, not a commitment to a
 * particular JSON payload. This presenter keeps HTTP field names and coin
 * rendering outside application code. Returning arrays directly from results
 * was considered, but that would make Application aware of one adapter's
 * serialization needs and weaken the layer boundary.
 */
final readonly class MachineSnapshotJsonPresenter
{
    public function __construct(
        private CoinJsonPresenter $coinPresenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function present(MachineSnapshot $snapshot): array
    {
        return [
            'machineId' => $snapshot->machineId()->value(),
            'insertedBalanceCoins' => $this->coinPresenter->coins($snapshot->insertedBalanceCents()),
            'hasPendingBalance' => $snapshot->hasPendingBalance(),
            'insertedCoins' => $this->coinPresenter->coinCounts($snapshot->insertedCoins()),
            'availableChangeCounts' => $this->coinPresenter->coinCounts($snapshot->availableChangeCounts()),
            'products' => array_map(
                fn (ProductSnapshot $product): array => [
                    'selector' => $product->selector()->value(),
                    'name' => $product->name(),
                    'priceCoins' => $this->coinPresenter->coins($product->priceCents()),
                    'quantity' => $product->quantity(),
                    'available' => $product->isAvailable(),
                ],
                $snapshot->products(),
            ),
        ];
    }
}
