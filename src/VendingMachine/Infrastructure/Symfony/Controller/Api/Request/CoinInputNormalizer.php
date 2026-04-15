<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

use InvalidArgumentException;

/**
 * Normalizes reviewer-facing coin literals into integer cents for application commands.
 */
final class CoinInputNormalizer
{
    public function coinCentsFromInsertPayload(JsonPayload $payload): int
    {
        if ($payload->has('coins')) {
            return $this->coinsToCents($payload->value('coins'));
        }

        if ($payload->has('coinCents')) {
            return $this->integerValue($payload->value('coinCents'), 'coinCents');
        }

        throw new InvalidArgumentException('Field "coins" or "coinCents" is required.');
    }

    /**
     * @param array<int|string, mixed> $coinCounts
     *
     * @return array<int, mixed>
     */
    public function coinCountKeysToCents(array $coinCounts): array
    {
        $normalized = [];

        foreach ($coinCounts as $coinKey => $quantity) {
            $normalized[$this->coinCountKeyToCents($coinKey)] = $quantity;
        }

        /** @var array<int, mixed> $normalized */
        return $normalized;
    }

    private function coinsToCents(mixed $value): int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_string($value)) {
            return $this->numericStringCoinsToCents($value);
        }

        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException('Field "coins" must be numeric.');
        }

        return $this->numericCoinsToCents((float) $value);
    }

    private function numericStringCoinsToCents(string $value): int
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Field "coins" must be numeric.');
        }

        return match ($value) {
            '0.05' => 5,
            '0.1', '0.10' => 10,
            '0.25' => 25,
            '1', '1.0', '1.00' => 100,
            default => throw new InvalidArgumentException(
                'Field "coins" must be one of 0.05, 0.10, 0.25, or 1.',
            ),
        };
    }

    private function numericCoinsToCents(float $value): int
    {
        $epsilon = 0.000001;

        return match (true) {
            abs($value - 0.05) < $epsilon => 5,
            abs($value - 0.10) < $epsilon => 10,
            abs($value - 0.25) < $epsilon => 25,
            abs($value - 1.0) < $epsilon => 100,
            default => throw new InvalidArgumentException(
                'Field "coins" must be one of 0.05, 0.10, 0.25, or 1.',
            ),
        };
    }

    private function coinCountKeyToCents(int|string $value): int
    {
        if (is_int($value)) {
            return $value === 1 ? 100 : $value;
        }

        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            throw new InvalidArgumentException('Available change denomination keys cannot be empty.');
        }

        if (is_numeric($normalizedValue) && str_contains($normalizedValue, '.')) {
            return $this->exactMoneyStringToCents(
                $normalizedValue,
                'Available change denomination keys must represent exact coin values.',
            );
        }

        if (ctype_digit($normalizedValue)) {
            return (int) $normalizedValue;
        }

        throw new InvalidArgumentException('Available change denomination keys must be numeric.');
    }

    private function exactMoneyStringToCents(string $value, string $errorMessage): int
    {
        if (!preg_match('/^(?<whole>\d+)\.(?<fraction>\d{1,2})$/', $value, $matches)) {
            throw new InvalidArgumentException($errorMessage);
        }

        $whole = (int) $matches['whole'];
        $fraction = $matches['fraction'];

        if (strlen($fraction) === 1) {
            $fraction .= '0';
        }

        return ($whole * 100) + (int) $fraction;
    }

    private function integerValue(mixed $value, string $field): int
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be an integer.', $field));
        }

        return $value;
    }
}
