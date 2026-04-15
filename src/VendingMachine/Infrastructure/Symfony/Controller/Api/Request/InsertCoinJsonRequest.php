<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for inserting one coin.
 */
final readonly class InsertCoinJsonRequest
{
    private function __construct(
        private int $coinCents,
    ) {
    }

    public static function fromPayload(JsonPayload $payload, CoinInputNormalizer $coinInputNormalizer): self
    {
        return new self($coinInputNormalizer->coinCentsFromInsertPayload($payload));
    }

    public function coinCents(): int
    {
        return $this->coinCents;
    }
}
