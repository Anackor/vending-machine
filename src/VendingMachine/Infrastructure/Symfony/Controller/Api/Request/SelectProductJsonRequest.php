<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for selecting a product.
 *
 * This passive DTO isolates the HTTP field name from the application command.
 * The selector is still validated again by the domain value object once it
 * enters the use case, but the mapper can give clients an HTTP-specific error
 * when the JSON field is missing or has the wrong type. A raw array in the
 * mapper was simpler, but less explicit as more endpoints are added.
 */
final readonly class SelectProductJsonRequest
{
    public function __construct(
        private string $selector,
    ) {
    }

    public function selector(): string
    {
        return $this->selector;
    }
}
