<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

/**
 * Validated HTTP contract for selecting a product.
 *
 * Tip: HTTP field checks stay here; selector semantics stay in the domain
 * value object. See docs/architecture/http-api-boundary.md for the trade-offs.
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
