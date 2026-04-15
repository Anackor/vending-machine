<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Validated HTTP JSON object payload.
 */
final readonly class JsonPayload
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(
        private array $values,
    ) {
    }

    /**
     * @throws JsonException
     */
    public static function fromRequest(Request $request): self
    {
        $content = trim($request->getContent());

        if ($content === '') {
            return new self([]);
        }

        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($payload) || ($payload !== [] && array_is_list($payload))) {
            throw new InvalidArgumentException('Request body must be a JSON object.');
        }

        /** @var array<string, mixed> $payload */
        return new self($payload);
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->values);
    }

    public function value(string $field): mixed
    {
        return $this->values[$field] ?? null;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function requiredObject(string $field): array
    {
        if (!$this->has($field)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $this->value($field);

        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a JSON object.', $field));
        }

        return $value;
    }

    public function requiredString(string $field): string
    {
        if (!$this->has($field)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $this->value($field);

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a string.', $field));
        }

        return $value;
    }
}
