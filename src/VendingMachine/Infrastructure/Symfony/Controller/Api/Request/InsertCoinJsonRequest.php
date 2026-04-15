<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for inserting one coin.
 *
 * Tip: this passive DTO documents adapter input before it becomes a command.
 * See docs/architecture/http-api-boundary.md for the trade-offs.
 */
final readonly class InsertCoinJsonRequest
{
    public function __construct(
        private int $coinCents,
    ) {
    }

    public function coinCents(): int
    {
        return $this->coinCents;
    }
}
