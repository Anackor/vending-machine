<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Presenter;

use VendingMachine\Application\Machine\Failure\MachineFailure;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;

/**
 * Presents application failures without leaking cent-based internals to HTTP clients.
 *
 * Application failures are intentionally stable and transport-neutral, so this
 * presenter adapts their context and messages to the reviewer-facing JSON
 * contract. Keeping this in infrastructure avoids teaching the application
 * layer about HTTP naming conventions. A richer typed error view model would be
 * another valid option, but this presenter keeps the current contract explicit
 * while leaving that future refactor isolated.
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
                'message' => $this->message($failure->code(), $failure->message()),
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

    private function message(MachineFailureCode $code, string $message): string
    {
        return match ($code) {
            MachineFailureCode::ExactChangeUnavailable,
            MachineFailureCode::InsufficientBalance,
            MachineFailureCode::InvalidServiceConfiguration,
            MachineFailureCode::UnsupportedCoin => preg_replace_callback(
                '/"(\d+)"/',
                fn (array $matches): string => sprintf('"%s"', $this->coinPresenter->coinKey((int) $matches[1])),
                $message,
            ) ?? $message,
            default => $message,
        };
    }
}
