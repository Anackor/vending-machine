<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter;

use VendingMachine\Application\Machine\Failure\MachineFailure;

/**
 * Presents application failures without leaking cent-based internals to HTTP clients.
 *
 * Tip: amounts are read from structured context, never parsed from messages.
 * See docs/architecture/http-api-boundary.md for the trade-offs.
 */
final readonly class MachineFailureJsonPresenter
{
    public function __construct(
        private CoinJsonPresenter $coinPresenter,
    ) {
    }

    /**
     * @return array{error: array{code: string, message: string, context: array<string, bool|float|int|string>}}
     */
    public function present(MachineFailure $failure): array
    {
        return [
            'error' => [
                'code' => $failure->code()->value,
                'message' => $failure->message(),
                'context' => $this->context($failure->context()),
            ],
        ];
    }

    /**
     * @param array<string, bool|float|int|string> $context
     *
     * @return array<string, bool|float|int|string>
     */
    private function context(array $context): array
    {
        $normalized = [];

        foreach ($context as $key => $value) {
            if ($key === 'coinCents' && is_int($value)) {
                $normalized['coins'] = $this->coinPresenter->coins($value);
                continue;
            }

            if (str_ends_with($key, 'Cents') && is_int($value)) {
                $normalized[sprintf('%sCoins', substr($key, 0, -5))] = $this->coinPresenter->coins($value);
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
