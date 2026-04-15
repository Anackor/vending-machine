<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter;

/**
 * Presents integer cents as reviewer-facing coin literals.
 */
final class CoinJsonPresenter
{
    public function coins(int $coinCents): int|float
    {
        if ($coinCents % 100 === 0) {
            return intdiv($coinCents, 100);
        }

        return round($coinCents / 100, 2);
    }

    /**
     * @param array<int|string, int> $coinCounts
     *
     * @return array<string, int>
     */
    public function coinCounts(array $coinCounts): array
    {
        $normalized = [];
        ksort($coinCounts);

        foreach ($coinCounts as $coinCents => $quantity) {
            $normalized[$this->coinKey((int) $coinCents)] = $quantity;
        }

        return $normalized;
    }

    public function coinKey(int $coinCents): string
    {
        $coins = $this->coins($coinCents);

        if (is_int($coins)) {
            return (string) $coins;
        }

        return number_format($coins, 2, '.', '');
    }
}
