<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter;

/**
 * Presents integer cents as reviewer-facing coin literals.
 *
 * Money stays as integer cents in the core to avoid floating point drift, but
 * the public HTTP contract is easier to review with coin values such as 0.25
 * and 1. This presenter centralizes that translation. The alternative was to
 * duplicate cents-to-coins formatting in every response method, which made the
 * transport layer harder to audit and easier to drift over time.
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
