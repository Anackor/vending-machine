<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter;

use VendingMachine\Application\Machine\Result\MachineSnapshot;
use VendingMachine\Application\Machine\Result\ProductSnapshot;

/**
 * Presents an application machine snapshot as the stable HTTP JSON shape.
 *
 * Tip: the application snapshot is not the JSON contract. See
 * docs/architecture/http-api-boundary.md for the trade-offs.
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
