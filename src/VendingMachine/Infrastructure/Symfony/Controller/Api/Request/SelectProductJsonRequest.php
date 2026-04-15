<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for selecting a product.
 */
final readonly class SelectProductJsonRequest
{
    private function __construct(
        private string $selector,
    ) {
    }

    public static function fromPayload(JsonPayload $payload): self
    {
        return new self($payload->requiredString('selector'));
    }

    public function selector(): string
    {
        return $this->selector;
    }
}
