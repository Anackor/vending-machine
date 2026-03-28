<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Command\ReturnInsertedMoneyCommand;
use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Command\ServiceMachineCommand;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;

/**
 * Parses HTTP JSON input and normalizes it into application commands and queries.
 */
final class MachineJsonRequestFactory
{
    public function createGetMachineStateQuery(): GetMachineStateQuery
    {
        return new GetMachineStateQuery();
    }

    public function createInsertCoinCommand(Request $request): InsertCoinCommand
    {
        $payload = $this->payload($request);

        return new InsertCoinCommand(
            $this->requiredCoinCents($payload),
        );
    }

    public function createReturnInsertedMoneyCommand(): ReturnInsertedMoneyCommand
    {
        return new ReturnInsertedMoneyCommand();
    }

    public function createServiceMachineCommand(Request $request): ServiceMachineCommand
    {
        $payload = $this->payload($request);

        return new ServiceMachineCommand(
            $this->requiredObject($payload, 'productQuantities'),
            $this->requiredObject($payload, 'availableChangeCounts'),
        );
    }

    public function createSelectProductCommand(Request $request): SelectProductCommand
    {
        $payload = $this->payload($request);

        return new SelectProductCommand(
            $this->requiredString($payload, 'selector'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        // The transport contract stays predictable by accepting only JSON objects at the boundary.
        $content = trim($request->getContent());

        if ($content === '') {
            return [];
        }

        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($payload) || ($payload !== [] && array_is_list($payload))) {
            throw new InvalidArgumentException('Request body must be a JSON object.');
        }

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<array-key, mixed>
     */
    private function requiredObject(array $payload, string $field): array
    {
        if (!array_key_exists($field, $payload)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $payload[$field];

        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a JSON object.', $field));
        }

        return $value;
    }

    private function intValue(mixed $value, string $field): int
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be an integer.', $field));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredCoinCents(array $payload): int
    {
        // The adapter accepts reviewer-friendly "coins" while keeping integer cents inside the core.
        if (array_key_exists('coins', $payload)) {
            return $this->normalizeCoinsToCents($payload['coins']);
        }

        if (array_key_exists('coinCents', $payload)) {
            return $this->intValue($payload['coinCents'], 'coinCents');
        }

        throw new InvalidArgumentException('Field "coins" or "coinCents" is required.');
    }

    private function normalizeCoinsToCents(mixed $value): int
    {
        // Input is normalized here once so the application layer never deals with decimal money.
        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_string($value)) {
            return $this->normalizeNumericStringCoinsToCents($value);
        }

        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException('Field "coins" must be numeric.');
        }

        return $this->normalizeNumericCoinsToCents((float) $value);
    }

    private function normalizeNumericStringCoinsToCents(string $value): int
    {
        // Only the exact challenge denominations are accepted; near matches are rejected, not rounded.
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

    private function normalizeNumericCoinsToCents(float $value): int
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

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(array $payload, string $field): string
    {
        if (!array_key_exists($field, $payload)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $payload[$field];

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a string.', $field));
        }

        return $value;
    }
}
