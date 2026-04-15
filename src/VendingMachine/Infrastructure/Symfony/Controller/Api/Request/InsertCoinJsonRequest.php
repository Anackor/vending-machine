<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for inserting one coin.
 *
 * This DTO captures the request shape after JSON parsing and before creating
 * the application command. It keeps transport validation close to Symfony
 * without making the command accept raw HTTP payloads. Using Symfony forms or
 * validation attributes would also work, but for this small API an explicit DTO
 * is easier to read and keeps framework configuration out of the use case path.
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
